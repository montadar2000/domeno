<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\RoundStatus;
use App\Enums\TeamSide;
use App\Models\DominoMatch;
use App\Models\Round;
use App\Models\ScoreEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScoringService
{
    public const WIN_SCORE = 151;

    public const MILS_SCORE = 100;

    public const MAX_POINTS = 200;

    public function startMatch(User $user, string $teamOneName, string $teamTwoName): DominoMatch
    {
        return DB::transaction(function () use ($user, $teamOneName, $teamTwoName) {
            DominoMatch::query()
                ->ownedBy($user)
                ->where('status', MatchStatus::Active)
                ->update(['status' => MatchStatus::Closed]);

            $match = DominoMatch::query()->create([
                'user_id' => $user->id,
                'team_one_name' => $this->nameOrDefault($teamOneName, 'الفريق الأول'),
                'team_two_name' => $this->nameOrDefault($teamTwoName, 'الفريق الثاني'),
                'status' => MatchStatus::Active,
            ]);

            $match->rounds()->create([
                'round_number' => 1,
                'status' => RoundStatus::Playing,
            ]);

            return $match->load('rounds');
        });
    }

    public function rename(DominoMatch $match, string $teamOneName, string $teamTwoName): DominoMatch
    {
        $this->assertActive($match);

        $match->update([
            'team_one_name' => $this->nameOrDefault($teamOneName, 'الفريق الأول'),
            'team_two_name' => $this->nameOrDefault($teamTwoName, 'الفريق الثاني'),
        ]);

        return $match;
    }

    public function close(DominoMatch $match): DominoMatch
    {
        $match->update(['status' => MatchStatus::Closed]);

        return $match;
    }

    public function addScore(DominoMatch $match, TeamSide $team, int $points): Round
    {
        return DB::transaction(function () use ($match, $team, $points) {
            $this->assertActive($match);

            if ($points < 1 || $points > self::MAX_POINTS) {
                throw ValidationException::withMessages([
                    'points' => 'النقاط لازم تكون بين 1 و 200.',
                ]);
            }

            $round = $this->lockCurrentRound($match);

            if ($round->status !== RoundStatus::Playing) {
                throw ValidationException::withMessages([
                    'points' => 'الجولة منتهية. ابدأ جولة جديدة.',
                ]);
            }

            $round->scores()->create([
                'team' => $team,
                'points' => $points,
            ]);

            $this->syncTotals($round);

            return $round->fresh(['scores']);
        });
    }

    public function undoLatest(DominoMatch $match): Round
    {
        return DB::transaction(function () use ($match) {
            $this->assertActive($match);

            $round = $this->lockCurrentRound($match);

            $entry = ScoreEntry::query()
                ->where('round_id', $round->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($entry === null) {
                throw ValidationException::withMessages([
                    'score' => 'ماكو تسجيل نتراجع عنه في هالجولة.',
                ]);
            }

            $entry->delete();
            $this->syncTotals($round);

            return $round->fresh(['scores']);
        });
    }

    public function startNextRound(DominoMatch $match): Round
    {
        return DB::transaction(function () use ($match) {
            $this->assertActive($match);

            $round = $this->lockCurrentRound($match);

            if ($round->status !== RoundStatus::Finished) {
                throw ValidationException::withMessages([
                    'round' => 'الجولة لسه مفتوحة.',
                ]);
            }

            return $match->rounds()->create([
                'round_number' => $round->round_number + 1,
                'status' => RoundStatus::Playing,
            ]);
        });
    }

    private function syncTotals(Round $round): void
    {
        $round->team_one_score = (int) $round->scores()->where('team', TeamSide::TeamOne)->sum('points');
        $round->team_two_score = (int) $round->scores()->where('team', TeamSide::TeamTwo)->sum('points');

        $winner = null;
        $milsTeam = null;

        if ($round->team_one_score >= self::MILS_SCORE && $round->team_two_score === 0) {
            $winner = TeamSide::TeamOne;
            $milsTeam = TeamSide::TeamTwo;
        } elseif ($round->team_two_score >= self::MILS_SCORE && $round->team_one_score === 0) {
            $winner = TeamSide::TeamTwo;
            $milsTeam = TeamSide::TeamOne;
        } elseif ($round->team_one_score >= self::WIN_SCORE) {
            $winner = TeamSide::TeamOne;
        } elseif ($round->team_two_score >= self::WIN_SCORE) {
            $winner = TeamSide::TeamTwo;
        }

        $round->mils_team = $milsTeam;

        if ($winner instanceof TeamSide) {
            $round->status = RoundStatus::Finished;
            $round->winner = $winner;
            $round->finished_at ??= now();
        } else {
            $round->status = RoundStatus::Playing;
            $round->winner = null;
            $round->finished_at = null;
        }

        $round->save();
    }

    private function lockCurrentRound(DominoMatch $match): Round
    {
        $round = Round::query()
            ->where('match_id', $match->id)
            ->orderByDesc('round_number')
            ->lockForUpdate()
            ->first();

        if ($round === null) {
            throw ValidationException::withMessages([
                'round' => 'ماكو جولة بهالجلسة.',
            ]);
        }

        return $round;
    }

    private function assertActive(DominoMatch $match): void
    {
        if ($match->status !== MatchStatus::Active) {
            throw ValidationException::withMessages([
                'match' => 'هذي الجلسة منتهية.',
            ]);
        }
    }

    private function nameOrDefault(string $name, string $default): string
    {
        $name = trim($name);

        return $name === '' ? $default : $name;
    }
}
