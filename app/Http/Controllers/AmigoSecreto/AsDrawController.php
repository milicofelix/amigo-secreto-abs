<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Jobs\SendDrawEmail;
use App\Jobs\SendDrawWebPush;
use App\Models\AmigoSecreto\AsDraw;
use App\Models\AmigoSecreto\Group;
use App\Services\DrawMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AsDrawController extends Controller
{
    public function store(Request $request, Group $group, DrawMatcher $matcher)
    {
        try {
            $draw = $group->withDrawLock(function (Group $locked) use ($request, $matcher) {
                if ($locked->draws()->active()->exists()) {
                    throw ValidationException::withMessages(['draw' => 'Este grupo já realizou o sorteio.']);
                }

                $pairs = $matcher->match($locked->participants()->get(['id', 'name']), $locked->restrictions()->get());
                $draw = $locked->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
                foreach ($pairs as $giver => $receiver) {
                    $draw->assignments()->create(['giver_id' => $giver, 'receiver_id' => $receiver]);
                }
                $draw->events()->create(['actor_id' => $request->user()->id, 'type' => 'confirmed']);

                DB::afterCommit(fn () => $this->notifyParticipants($draw));

                return $draw;
            });
        } catch (ValidationException $exception) {
            return redirect()->route('groups.edit', $group)->with('error', $exception->validator->errors()->first());
        }

        return redirect()
            ->route($draw->group->canRevealCollectively() ? 'dashboard.revealed-friends' : 'draw.show', $group)
            ->with('completed_draw_group_id', $group->id);
    }

    public function show(Group $group)
    {
        $draw = $group->draws()->active()
            ->latest('id')
            ->with('assignments.giver:id,name,gender,first_revealed_at,shared_gift_idea,bought_gift')
            ->first();

        if ($draw && $group->canRevealCollectively()) {
            return redirect()->route('dashboard.revealed-friends', $group);
        }

        return view('amigo-secreto.draws.show', compact('group', 'draw'));
    }

    public function cancel(Request $request, Group $group)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $group->withDrawLock(function (Group $locked) use ($request, $data) {
            $draw = $locked->draws()->active()->latest('id')->first();
            if (!$draw || $locked->canRevealCollectively()) {
                throw ValidationException::withMessages(['reason' => 'Só é possível cancelar um sorteio ativo antes da revelação coletiva.']);
            }

            $draw->update(['cancelled_at' => now()]);
            $draw->events()->create([
                'actor_id' => $request->user()->id,
                'type' => 'cancelled',
                'details' => ['reason' => $data['reason']],
            ]);
            $locked->participants()->update([
                'first_revealed_at' => null,
                'bought_gift' => false, 'bought_gift_at' => null,
                'shared_gift_idea' => false, 'shared_gift_idea_at' => null,
            ]);
        });

        return redirect()->route('groups.edit', $group)->with('success', 'Sorteio cancelado. A lista e as restrições podem ser ajustadas.');
    }

    private function notifyParticipants(AsDraw $draw): void
    {
        $jobs = ['email' => new SendDrawEmail($draw->id)];
        if (SendDrawWebPush::enabled()) {
            $jobs['push'] = new SendDrawWebPush($draw->id);
        }

        foreach ($jobs as $channel => $job) {
            try {
                dispatch($job);
            } catch (\Throwable) {
                $draw->events()->create(['type' => 'notification_dispatch_failed', 'details' => ['channel' => $channel]]);
                Log::warning('Falha ao iniciar os avisos do sorteio.', ['draw_id' => $draw->id, 'channel' => $channel]);
            }
        }
    }
}
