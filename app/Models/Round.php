<?php

namespace App\Models;

use App\Enums\RoundStatus;
use App\Enums\TeamSide;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'match_id',
    'round_number',
    'team_one_score',
    'team_two_score',
    'winner',
    'mils_team',
    'status',
    'finished_at',
])]
class Round extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Round $round): void {
            if ($round->user_id !== null || $round->match_id === null) {
                return;
            }

            $round->user_id = DominoMatch::query()->whereKey($round->match_id)->value('user_id');
        });
    }

    protected $attributes = [
        'team_one_score' => 0,
        'team_two_score' => 0,
        'status' => 'playing',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'team_one_score' => 'integer',
            'team_two_score' => 'integer',
            'winner' => TeamSide::class,
            'mils_team' => TeamSide::class,
            'status' => RoundStatus::class,
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dominoMatch(): BelongsTo
    {
        return $this->belongsTo(DominoMatch::class, 'match_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ScoreEntry::class);
    }

    public function scoreFor(TeamSide $team): int
    {
        return $team === TeamSide::TeamOne
            ? $this->team_one_score
            : $this->team_two_score;
    }
}
