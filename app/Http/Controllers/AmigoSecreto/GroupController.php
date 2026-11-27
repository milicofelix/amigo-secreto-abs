<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\DrawEvent;
use App\Models\AmigoSecreto\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = Group::selectedYear($request->query('year'));
        $groups = Group::createdBy($request->user())->forYear($selectedYear)
            ->withExists([
                'draws as has_draw_history' => fn ($query) => $query->where('status', 'completed'),
                'draws as has_active_draw' => fn ($query) => $query->active(),
            ])->latest()->get();

        return view('amigo-secreto.groups.index', compact('groups', 'selectedYear'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('amigo-secreto.groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'budget_min'     => 'nullable|numeric',
            'budget_max'     => 'nullable|numeric',
            'event_date'     => 'nullable|date',
            'event_location' => 'nullable|string|max:255',
        ]);

        $group = new Group($validated);
        $group->user()->associate($request->user());
        $group->save();

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        return redirect()->route('groups.edit', $group);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        $hasCompletedDraw = $group->draws()->active()->exists();
        $history = DrawEvent::whereIn('draw_id', $group->draws()->select('id'))->with('actor:id,name')
            ->latest('created_at')->latest('id')->simplePaginate(10, pageName: 'historico');

        return view('amigo-secreto.groups.edit', compact('group', 'hasCompletedDraw', 'history'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'budget_min'     => 'nullable|numeric',
            'budget_max'     => 'nullable|numeric',
            'event_date'     => 'nullable|date',
            'event_location' => 'nullable|string|max:255',
        ]);

        $group->withDrawLock(function (Group $locked) use ($validated) {
            if (array_key_exists('event_date', $validated) && $validated['event_date'] !== $locked->event_date
                && $locked->draws()->active()->exists()) {
                throw ValidationException::withMessages(['event_date' => 'A data do evento fica bloqueada após o sorteio.']);
            }

            $locked->update($validated);
        });
        $group->refresh();

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo atualizado com sucesso!');
    }

    public function scheduleReveal(Request $request, Group $group)
    {
        $data = $request->validate(
            ['collective_reveal_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now']],
            ['collective_reveal_at.after' => 'Escolha uma data e hora futuras para a revelação coletiva.'],
        );

        $group->withDrawLock(function (Group $locked) use ($data) {
            if ($locked->collectiveRevealAt() && $locked->draws()->active()->exists()) {
                throw ValidationException::withMessages(['collective_reveal_at' => 'A revelação já está agendada para este sorteio.']);
            }

            $locked->update(['collective_reveal_at' => $data['collective_reveal_at']]);
        });

        return redirect()->route('groups.edit', $group)->with('success', 'Revelação coletiva agendada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->withDrawLock(function (Group $locked) {
            if ($locked->draws()->where('status', 'completed')->exists()) {
                throw ValidationException::withMessages(['group' => 'Grupos com histórico de sorteio não podem ser excluídos.']);
            }

            $locked->delete();
        });

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo excluído com sucesso!');
    }

}
