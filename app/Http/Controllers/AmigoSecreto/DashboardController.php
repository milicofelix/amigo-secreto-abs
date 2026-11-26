<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use App\Models\AmigoSecreto\SecretReveal;
use App\Models\AmigoSecreto\AccessLog; 

class DashboardController extends Controller
{
    public function index(Group $group)
    {
        $currentGroup = $group;

        // === Cálculos do dashboard ===
        $participants = Participant::where('group_id', $currentGroup->id)->count();

        $revealed = SecretReveal::whereHas('participant', function($q) use ($currentGroup){
            $q->where('group_id', $currentGroup->id);
        })->count();

        $notRevealed = $participants - $revealed;
        $percent = $participants > 0 ? round(($revealed / $participants) * 100, 1) : 0;

        $latestReveals = SecretReveal::whereHas('participant', function($q) use ($currentGroup){
            $q->where('group_id', $currentGroup->id);
        })->orderBy('revealed_at', 'desc')->take(5)->get();

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
         // 🔒 TRAVA DEFINITIVA
        if (!$group->eventIsOver()) {
            return redirect()
                ->route('amigo.dashboard', $group->id)
                ->with('warning', 'Os amigos secretos ainda não podem ser revelados.');
        }
        
        $results = \DB::table('assignments as a')
            ->join('participants as g', 'g.id', '=', 'a.giver_id')
            ->join('participants as r', 'r.id', '=', 'a.receiver_id')
            ->where('g.group_id', $group->id)
            ->orderBy('g.name')
            ->select([
                'g.name as quem_tirou',
                'r.name as quem_foi_tirado',
            ])
            ->get();

        return view('amigo-secreto.dashboard.revealed-friends', [
            'group'   => Group::findOrFail($group->id),
            'results' => $results
        ]);
    }
}
