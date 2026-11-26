<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\AsDraw;
use App\Models\AmigoSecreto\Assignment;
use App\Models\AmigoSecreto\Restriction;
use Illuminate\Support\Facades\DB;
use App\Mail\SecretFriendNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendDrawWebPush;
use App\Models\AmigoSecreto\Participant;

class AsDrawController extends Controller
{
 
    public function store(Group $group)
    {
         // 🔒 Verifica se já existe sorteio para este grupo
        $existingDraw = AsDraw::where('group_id', $group->id)
            ->where('status', 'completed')
            ->first();

        if ($existingDraw) {
            return back()->with('error', 'Este grupo já realizou o sorteio. Não é possível sortear novamente.');
        }
        
       $participants = Participant::where('group_id', $group->id)->get();

        if ($participants->count() < 2) {
            return back()->with('error', 'São necessários pelo menos 2 participantes para realizar o sorteio.');
        }

        $restrictions = Restriction::where('group_id', $group->id)->get();

        $attempts = 0;
        $maxAttempts = 20;
        $success = false;
        $result = null;

        // 🔁 Tenta gerar um sorteio válido até o limite de tentativas
        do {
            $attempts++;
            $result = $this->generateAssignments($participants, $restrictions);

            if (!$result) {
                continue; // falha lógica (restrições ou auto-sorteio)
            }

            // 🔎 Verifica duplicidades e auto-sorteio
            $receivers = array_values($result);
            $uniqueReceivers = array_unique($receivers);
            $hasDuplicates = count($receivers) !== count($uniqueReceivers);
            $hasSelfDraws = collect($result)->some(fn($r, $g) => $r === $g);

            if (!$hasDuplicates && !$hasSelfDraws) {
                $success = true;
            }

        } while (!$success && $attempts < $maxAttempts);

        if (!$success) {
            return back()->with('error', 'Não foi possível gerar um sorteio válido após várias tentativas. Tente novamente.');
        }

        // 🔒 Transação segura — grava tudo ou nada
        DB::beginTransaction();

        try {
            Group::whereKey($group->id)->lockForUpdate()->firstOrFail();
            if ($group->draws()->where('status', 'completed')->exists()) {
                DB::rollBack();

                return back()->with('error', 'Este grupo já realizou o sorteio.');
            }

            $currentParticipantIds = $group->participants()->pluck('id')->all();
            if (count($currentParticipantIds) !== count($result) || array_diff($currentParticipantIds, array_keys($result))) {
                DB::rollBack();

                return back()->with('error', 'A lista de participantes mudou. Tente realizar o sorteio novamente.');
            }

            $draw = AsDraw::create([
                'group_id' => $group->id,
                'drawn_at' => now(),
                'status'   => 'completed',
            ]);

            foreach ($result as $giver => $receiver) {
                Assignment::create([
                    'draw_id'     => $draw->id,
                    'giver_id'    => $giver,
                    'receiver_id' => $receiver,
                ]);

            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao realizar sorteio: ' . $e->getMessage());

            return back()->with('error', 'Ocorreu um erro inesperado ao realizar o sorteio. Tente novamente.');
        }

        $recipients = $group->participants()->whereKey(array_keys($result))
            ->where('draw_email_enabled', true)->whereNotNull('email')->get(['id', 'email']);

        foreach ($recipients as $recipient) {
            if (!filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            try {
                Mail::to($recipient->email)->queue((new SecretFriendNotification)->onQueue('emails'));
            } catch (\Throwable) {
                Log::warning('Não foi possível enfileirar o aviso de sorteio por e-mail.', ['draw_id' => $draw->id]);
            }
        }

        if (SendDrawWebPush::enabled()) {
            try {
                SendDrawWebPush::dispatch($draw->id);
            } catch (\Throwable) {
                Log::error('Não foi possível processar o Web Push.', ['draw_id' => $draw->id]);
            }
        }

        return redirect()
            ->route($group->eventIsOver() ? 'dashboard.revealed-friends' : 'draw.show', ['group' => $group->id])
            ->with('completed_draw_group_id', $group->id);
    }

    public function show(Group $group)
    {
        $draw = $group->draws()
            ->where('status', 'completed')
            ->latest()
            ->with('assignments.giver:id,name,gender,first_revealed_at,shared_gift_idea,bought_gift')
            ->first();

        if ($draw && $group->eventIsOver()) {
            return redirect()->route('dashboard.revealed-friends', $group);
        }

        return view('amigo-secreto.draws.show', compact('group', 'draw'));
    }

    /**
     * Gera sorteio respeitando restrições (derangement com exclusões)
     */
    private function generateAssignments($participants, $restrictions)
    {
        $ids = $participants->pluck('id')->toArray();
        $receivers = $ids;
        shuffle($receivers);

        $restrictionsMap = [];
        foreach ($restrictions as $r) {
            $restrictionsMap[$r->participant_id][] = $r->cannot_draw_id;
        }

        $result = [];
        $attempts = 0;
        $maxAttempts = 1000;

        do {
            $valid = true;
            $result = [];
            $receivers = $ids;
            shuffle($receivers);

            foreach ($ids as $giver) {
                $receiver = array_shift($receivers);

                // Regra 1: não pode tirar a si mesmo
                if ($giver === $receiver) {
                    $valid = false;
                    break;
                }

                // Regra 2: não pode tirar restrições
                if (!empty($restrictionsMap[$giver]) && in_array($receiver, $restrictionsMap[$giver])) {
                    $valid = false;
                    break;
                }

                $result[$giver] = $receiver;
            }

            $attempts++;
        } while (!$valid && $attempts < $maxAttempts);

        return $valid ? $result : false;
    }

}
