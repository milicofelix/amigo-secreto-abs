<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;

class DashboardController extends Controller
{
    public function index(Group $group)
    {
        $currentGroup = $group;

        // === Cálculos do dashboard ===
        $participants = Participant::where('group_id', $currentGroup->id)->count();

        $reveals = $currentGroup->participants()->whereNotNull('first_revealed_at');
        $revealed = (clone $reveals)->count();
        $notRevealed = $participants - $revealed;
        $percent = $participants > 0 ? round(($revealed / $participants) * 100, 1) : 0;
        $latestReveals = $reveals->latest('first_revealed_at')->take(5)->get(['name', 'first_revealed_at']);

        //$sharedIdeas = Participant::where('group_id', $currentGroup->id)->where('shared_gift_idea', true)->count();
        //$boughtGifts = Participant::where('group_id', $currentGroup->id)->where('bought_gift', true)->count();
        $giftIdeaPosted = Participant::where('group_id', $currentGroup->id)->where('shared_gift_idea', 1)->count();
        $giftBought = Participant::where('group_id', $currentGroup->id)->where('bought_gift', 1)->count();

        $latestGiftIdeas = Participant::where('group_id', $currentGroup->id)
                            ->where('shared_gift_idea', true)
                            ->orderBy('shared_gift_idea_at', 'desc')
                            ->take(5)
                            ->get();

        $latestBoughtGifts = Participant::where('group_id', $currentGroup->id)
                                ->where('bought_gift', true)
                                ->orderBy('bought_gift_at', 'desc')
                                ->take(5)
                                ->get();

        return view('amigo-secreto.dashboard.index', [
            'group' => $currentGroup,
            'participants' => $participants,
            'revealed' => $revealed,
            'notRevealed' => $notRevealed,
            'percent' => $percent,
            'latestReveals' => $latestReveals,
            'giftIdeaPosted' => $giftIdeaPosted,
            'giftBought' => $giftBought,
            'latestGiftIdeas' => $latestGiftIdeas,
            'latestBoughtGifts' => $latestBoughtGifts
        ]);
    }

    public function startDraw(Group $group)
    {
        // pega todos participantes do grupo
        $participants = Participant::where('group_id', $group->id)->get();

        if ($participants->isEmpty()) {
            return back()->with('error', 'Nenhum participante cadastrado.');
        }

        // nome e id para a roleta
        $nomes = $participants->map(fn($p) => [
            'id' => $p->id,
            'nome' => $p->name
        ]);

        // sorteia um
        $sorteado = $participants->random();

        return view('amigo-secreto.dashboard.sortear', [
            'group'     => $group,
            'nomes'     => $nomes,
            'sorteado'  => $sorteado
        ]);
    }

    public function home(Request $request)
    {
        if (!$request->user()?->is_organizer) {
            return view('amigo-secreto.home');
        }

        if ($request->routeIs('home') && $request->user()->can('access-super-admin')) {
            return redirect()->route('super-admin.groups');
        }

        $year = Group::selectedYear($request->query('year'));

        return redirect()->route('dashboard.select', ['year' => $year]);
    }

    public function selectGroup(Request $request)
    {
        $selectedYear = Group::selectedYear($request->query('year'));
        $groups = Group::createdBy($request->user())->forYear($selectedYear)->withCount('participants')->orderBy('name')->get();

        return view('amigo-secreto.dashboard.select-group', compact('groups', 'selectedYear'));
    }

    public function revealedFriends(Group $group)
    {
        return $group->withDrawLock(function (Group $locked) {
            if (!$locked->canRevealCollectively()) {
                return redirect()
                    ->route('draw.show', $locked)
                    ->with('warning', 'A revelação coletiva ainda não está disponível.');
            }

            $drawId = $locked->draws()->active()->latest('id')->value('id');
            $results = \DB::table('assignments as a')
                ->join('participants as g', 'g.id', '=', 'a.giver_id')
                ->join('participants as r', 'r.id', '=', 'a.receiver_id')
                ->where('a.draw_id', $drawId)
                ->where('g.group_id', $locked->id)->where('r.group_id', $locked->id)
                ->orderBy('g.name')
                ->select(['g.name as quem_tirou', 'r.name as quem_foi_tirado'])
                ->get();

            return view('amigo-secreto.dashboard.revealed-friends', ['group' => $locked, 'results' => $results]);
        });
    }
}
