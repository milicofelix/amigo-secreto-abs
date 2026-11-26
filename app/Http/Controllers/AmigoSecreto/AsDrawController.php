<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\AsDraw;
use App\Models\AmigoSecreto\Assignment;
use App\Models\AmigoSecreto\Restriction;
use Illuminate\Support\Facades\DB;
use App\Mail\SecretFriendNotification;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendWhatsAppMessageJob;
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
            $draw = AsDraw::create([
                'group_id' => $group->id,
                'drawn_at' => now(),
                'status'   => 'completed',
            ]);

            foreach ($result as $giver => $receiver) {
                $assignment = Assignment::create([
                    'draw_id'     => $draw->id,
                    'giver_id'    => $giver,
                    'receiver_id' => $receiver,
                ]);

                $giverModel = $assignment->giver;
                $receiverModel = $assignment->receiver;

                if ($giverModel && $giverModel->email) {
                    try {
                            Mail::to($giverModel->email)
                                ->queue((new SecretFriendNotification($giverModel, $receiverModel, $group))
                                ->onQueue('emails'));

                        } catch (\Throwable $e) {
                            Log::warning('[MAIL] Falha ao enviar e-mail para ' . $giverModel->email . ': ' . $e->getMessage());
                        }
                }
                // 💬 Monta mensagem para WhatsApp
                // if ($giverModel && $giverModel->whatsapp) {
                //     $msg = "🎁 Olá {$giverModel->name}! O sorteio do grupo *{$group->name}* foi realizado!\n"
                //         . "Você tirou 👉 *{$receiverModel->name}*.\n\n"
                //         . "🎯 Lista de desejos: {$receiverModel->wishlist}\n"
                //         . "Boa sorte e Feliz Amigo Secreto! 🎉";

                //     // ⏳ Enfileira envio (com atraso para evitar flood)
                //     dispatch(new SendWhatsAppMessageJob($giverModel->whatsapp, $msg))
                //         ->delay(now()->addSeconds(2));
                // }
            }


            DB::commit();

            return redirect()
                ->route($group->eventIsOver() ? 'dashboard.revealed-friends' : 'draw.show', ['group' => $group->id])
                ->with('completed_draw_group_id', $group->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao realizar sorteio: ' . $e->getMessage());

            return back()->with('error', 'Ocorreu um erro inesperado ao realizar o sorteio. Tente novamente.');
        }
    }

    public function show(Group $group)
    {
         // 🔴 REGRA DE NEGÓCIO CENTRAL
        // Se o evento já passou, não existe mais segredo
        if ($group->eventIsOver()) {
            return redirect()->route('dashboard.revealed-friends', $group->id);
        }

        // Busca o último sorteio do grupo
        $draw = $group->draws()
            ->latest()
            ->with('assignments.giver', 'assignments.receiver')
            ->first();

        if (!$draw) {
            abort(404, 'Sorteio não encontrado para este grupo.');
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
