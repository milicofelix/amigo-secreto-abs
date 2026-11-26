<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SuperAdminGroupsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:1000,9999'],
            'creator' => ['nullable', 'string', 'regex:/\A(?:unassigned|[1-9][0-9]{0,17})\z/'],
            'draw' => ['nullable', Rule::in(['completed', 'pending'])],
        ]);
        $completed = fn ($query) => $query->where('status', 'completed');
        $query = Group::select('id', 'user_id', 'name', 'event_date', 'created_at')
            ->with('user:id,name')
            ->withCount('participants')
            ->withExists(['draws as has_completed_draw' => $completed]);

        if (isset($filters['year'])) {
            $query->forYear((int) $filters['year']);
        }
        if (($filters['creator'] ?? null) === 'unassigned') {
            $query->whereNull('user_id');
        } elseif (isset($filters['creator'])) {
            $query->where('user_id', $filters['creator']);
        }
        if (($filters['draw'] ?? null) === 'completed') {
            $query->whereHas('draws', $completed);
        } elseif (($filters['draw'] ?? null) === 'pending') {
            $query->whereDoesntHave('draws', $completed);
        }

        $groups = $query->orderByRaw('COALESCE(event_date, created_at) DESC')->orderByDesc('id')
            ->paginate(20)->withQueryString();
        $years = Group::query()
            ->selectRaw('SUBSTR(COALESCE(event_date, created_at), 1, 4) AS year')
            ->distinct()->pluck('year')->filter()->sortDesc();
        $creators = User::whereIn('id', Group::query()->select('user_id')->whereNotNull('user_id'))
            ->orderBy('name')->orderBy('id')->get(['id', 'name']);
        $confirmations = DB::table('group_creator_confirmations')->orderByDesc('id')
            ->simplePaginate(10, ['group_name', 'creator_name', 'confirmed_at'], 'confirmacoes')->withQueryString();

        return view('amigo-secreto.super-admin.groups', compact('groups', 'years', 'creators', 'filters', 'confirmations'));
    }
}
