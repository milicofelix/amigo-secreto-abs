<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ParticipantImportController extends Controller
{
    public function create(Request $request, Group $group)
    {
        $this->authorizeDestination($request, $group);

        $year = $group->eventYear() - 1;
        $sources = $this->sources($group)->withCount('participants')->orderBy('name')->get();
        $source = $request->filled('source')
            ? $sources->find($request->integer('source'))
            : ($sources->count() === 1 ? $sources->first() : null);
        $participants = $source?->participants()->orderBy('name')->get() ?? collect();
        $alreadyImported = $group->participants()->whereNotNull('imported_from_participant_id')
            ->pluck('imported_from_participant_id')->all();
        $hasCompletedDraw = $group->draws()->where('status', 'completed')->exists();

        return view('amigo-secreto.participants.import', compact(
            'group', 'year', 'sources', 'source', 'participants', 'alreadyImported', 'hasCompletedDraw'
        ));
    }

    public function store(Request $request, Group $group)
    {
        $this->authorizeDestination($request, $group);
        $data = $request->validate([
            'source' => ['required', 'integer'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['required', 'integer', 'distinct'],
        ], [
            'participants.required' => 'Selecione pelo menos um participante.',
        ]);

        [$added, $skipped, $unreadableEmails] = DB::transaction(function () use ($request, $group, $data) {
            $destination = Group::lockForUpdate()->findOrFail($group->id);
            $this->authorizeDestination($request, $destination);

            if ($destination->draws()->where('status', 'completed')->exists()) {
                throw ValidationException::withMessages(['source' => 'Este grupo já realizou o sorteio.']);
            }

            $source = $this->sources($destination)->find($data['source']);
            $participants = $source?->participants()->whereKey($data['participants'])->get();
            if (!$participants || $participants->count() !== count($data['participants'])) {
                throw ValidationException::withMessages(['source' => 'Escolha participantes de um grupo do ano anterior.']);
            }

            $existing = $destination->participants()->get();
            $added = 0;
            $unreadableEmails = 0;

            foreach ($participants as $participant) {
                if ($existing->contains(fn (Participant $current) =>
                    $current->imported_from_participant_id === $participant->id
                    || $this->sameRegistration($current, $participant)
                )) {
                    continue;
                }

                $email = $participant->email;
                if ($email === null && filled($participant->getRawOriginal('email'))) {
                    $unreadableEmails++;
                }

                $copy = $destination->participants()->make([
                    ...$participant->only(['name', 'gender', 'whatsapp', 'photo']),
                    'email' => $email,
                    'secret_key' => Str::upper(Str::random(12)),
                ]);
                $copy->forceFill(['imported_from_participant_id' => $participant->id])->save();
                $existing->push($copy);
                $added++;
            }

            return [$added, $participants->count() - $added, $unreadableEmails];
        });

        $message = $added === 1 ? '1 participante importado.' : "{$added} participantes importados.";
        if ($skipped > 0) {
            $message .= $skipped === 1
                ? ' 1 cadastro já existente foi ignorado.'
                : " {$skipped} cadastros já existentes foram ignorados.";
        }

        $response = redirect()->route('groups.edit', $group)->with('success', $message);
        if ($unreadableEmails > 0) {
            $response->with('participant_import_warning',
                "Não foi possível ler {$unreadableEmails} e-mail(s) do grupo anterior. Confira os participantes importados e preencha os e-mails em branco, se necessário.");
        }

        return $response;
    }

    private function authorizeDestination(Request $request, Group $group): void
    {
        abort_unless($group->user_id === $request->user()?->id, 403);
    }

    private function sources(Group $group): Builder
    {
        return Group::forYear($group->eventYear() - 1)->where('user_id', $group->user_id);
    }

    private function sameRegistration(Participant $current, Participant $source): bool
    {
        return Str::lower(Str::squish($current->name)) === Str::lower(Str::squish($source->name))
            && Str::lower(trim($current->email ?? '')) === Str::lower(trim($source->email ?? ''))
            && preg_replace('/\D/', '', $current->whatsapp ?? '') === preg_replace('/\D/', '', $source->whatsapp ?? '');
    }
}
