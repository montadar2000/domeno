<?php

namespace App\Http\Controllers;

use App\Enums\MatchStatus;
use App\Enums\TeamSide;
use App\Http\Requests\StoreMatchRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Models\DominoMatch;
use App\Services\ScoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;

class MatchController extends Controller
{
    public function index(): View
    {
        $match = $this->withWinCounts(
            request()->user()
                ->matches()
                ->where('status', MatchStatus::Active)
                ->latest('id')
                ->with(['rounds.scores']),
        )->first();

        $round = $match?->rounds->sortByDesc('round_number')->first();

        if ($match === null || $round === null) {
            return view('matches.start');
        }

        return view('matches.board', [
            'match' => $match,
            'round' => $round,
            'entries' => $round->scores->sortByDesc('id')->values(),
            'winScore' => ScoringService::WIN_SCORE,
        ]);
    }

    public function store(StoreMatchRequest $request, ScoringService $scoring): RedirectResponse
    {
        $scoring->startMatch(
            $request->user(),
            $request->string('team_one_name')->toString(),
            $request->string('team_two_name')->toString(),
        );

        return redirect()->route('home');
    }

    public function update(UpdateMatchRequest $request, DominoMatch $match, ScoringService $scoring): RedirectResponse
    {
        $scoring->rename(
            $match,
            $request->string('team_one_name')->toString(),
            $request->string('team_two_name')->toString(),
        );

        return redirect()->route('home')->with('status', 'تم تحديث الأسماء.');
    }

    public function close(DominoMatch $match, ScoringService $scoring): RedirectResponse
    {
        $scoring->close($match);

        return redirect()->route('home')->with('status', 'انتهت الجلسة.');
    }

    public function history(): View
    {
        $matches = $this->withWinCounts(
            request()->user()
                ->matches()
                ->with(['rounds' => fn ($query) => $query->orderBy('round_number')])
                ->latest('id'),
        )->get();

        return view('matches.history', [
            'matches' => $matches,
            'winScore' => ScoringService::WIN_SCORE,
        ]);
    }

    /**
     * @param  Builder<DominoMatch>|HasMany<DominoMatch, DominoMatch>  $query
     * @return Builder<DominoMatch>|HasMany<DominoMatch, DominoMatch>
     */
    private function withWinCounts(Builder|HasMany $query): Builder|HasMany
    {
        return $query->withCount([
            'rounds as team_one_wins' => fn (Builder $wins) => $wins->where('winner', TeamSide::TeamOne),
            'rounds as team_two_wins' => fn (Builder $wins) => $wins->where('winner', TeamSide::TeamTwo),
        ]);
    }
}
