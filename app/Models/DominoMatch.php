<?php

namespace App\Models;

use App\Enums\MatchStatus;
use App\Enums\TeamSide;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A sitting of two teams. The class is not named Match because that word is reserved in PHP.
 */
#[Fillable(['user_id', 'team_one_name', 'team_two_name', 'status'])]
class DominoMatch extends Model
{
    protected $table = 'matches';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MatchStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<DominoMatch>  $query
     * @return Builder<DominoMatch>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class, 'match_id');
    }

    public function currentRound(): HasOne
    {
        return $this->hasOne(Round::class, 'match_id')->latestOfMany('round_number');
    }

    public function nameFor(?TeamSide $team): string
    {
        return match ($team) {
            TeamSide::TeamOne => $this->team_one_name,
            TeamSide::TeamTwo => $this->team_two_name,
            null => '',
        };
    }

    public function winsFor(TeamSide $team): int
    {
        return $this->rounds()->where('winner', $team)->count();
    }
}
