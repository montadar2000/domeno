<?php

namespace App\Http\Controllers;

use App\Enums\TeamSide;
use App\Http\Requests\StoreScoreRequest;
use App\Models\DominoMatch;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;

class ScoreController extends Controller
{
    public function store(StoreScoreRequest $request, DominoMatch $match, ScoringService $scoring): RedirectResponse
    {
        $scoring->addScore(
            $match,
            $request->enum('team', TeamSide::class),
            $request->integer('points'),
        );

        return redirect()->route('home');
    }

    public function destroyLatest(DominoMatch $match, ScoringService $scoring): RedirectResponse
    {
        $scoring->undoLatest($match);

        return redirect()->route('home')->with('status', 'تم التراجع عن آخر تسجيل.');
    }

    public function storeRound(DominoMatch $match, ScoringService $scoring): RedirectResponse
    {
        $scoring->startNextRound($match);

        return redirect()->route('home')->with('status', 'بدأت جولة جديدة.');
    }
}
