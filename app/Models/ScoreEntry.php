<?php

namespace App\Models;

use App\Enums\TeamSide;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'round_id', 'team', 'points'])]
class ScoreEntry extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ScoreEntry $entry): void {
            if ($entry->user_id !== null || $entry->round_id === null) {
                return;
            }

            $entry->user_id = Round::query()->whereKey($entry->round_id)->value('user_id');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'team' => TeamSide::class,
            'points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }
}
