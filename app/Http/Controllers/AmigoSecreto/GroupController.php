<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = Group::selectedYear($request->query('year'));
        $groups = Group::forYear($selectedYear)->latest()->get();

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

        //$validated['user_id'] = Auth::id();
        $group = Group::create($validated);

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        // $this->authorizeGroup($group);
        return view('amigo-secreto.groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $this->authorizeGroup($group);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'budget_min'     => 'nullable|numeric',
            'budget_max'     => 'nullable|numeric',
            'event_date'     => 'nullable|date',
            'event_location' => 'nullable|string|max:255',
        ]);

        $group->update($validated);

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $this->authorizeGroup($group);
        $group->delete();

        return redirect()->route('groups.index', ['year' => $group->eventYear()])->with('success', 'Grupo excluído com sucesso!');
    }

     private function authorizeGroup(Group $group)
    {
        abort_unless($group->user_id === Auth::id(), 403, 'Acesso negado.');
    }
}
