<?php

namespace Tests\Feature;

use App\Enums\RoundStatus;
use App\Enums\TeamSide;
use App\Models\DominoMatch;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ScoringTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_adding_points_accumulates_for_the_chosen_team(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $scoring->addScore($match, TeamSide::TeamOne, 20);
        $scoring->addScore($match, TeamSide::TeamTwo, 15);
        $round = $scoring->addScore($match, TeamSide::TeamOne, 10);

        $this->assertSame(30, $round->team_one_score);
        $this->assertSame(15, $round->team_two_score);
        $this->assertSame(RoundStatus::Playing, $round->status);
        $this->assertNull($round->winner);
        $this->assertSame($this->user->id, $match->user_id);
        $this->assertSame($this->user->id, $round->user_id);
        $this->assertSame(
            [$this->user->id],
            $round->scores()->pluck('user_id')->unique()->values()->all(),
        );
    }

    public function test_score_below_151_keeps_the_round_open(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $scoring->addScore($match, TeamSide::TeamTwo, 1);
        $round = $scoring->addScore($match, TeamSide::TeamOne, 150);

        $this->assertSame(RoundStatus::Playing, $round->status);
        $this->assertSame(0, $match->winsFor(TeamSide::TeamOne));
    }

    public function test_crossing_151_finishes_the_round_and_counts_the_win(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $scoring->addScore($match, TeamSide::TeamTwo, 1);
        $scoring->addScore($match, TeamSide::TeamOne, 150);
        $round = $scoring->addScore($match, TeamSide::TeamOne, 1);

        $this->assertSame(151, $round->team_one_score);
        $this->assertSame(RoundStatus::Finished, $round->status);
        $this->assertSame(TeamSide::TeamOne, $round->winner);
        $this->assertNotNull($round->finished_at);
        $this->assertSame(1, $match->winsFor(TeamSide::TeamOne));
        $this->assertSame(0, $match->winsFor(TeamSide::TeamTwo));
    }

    public function test_points_cannot_be_added_after_the_round_is_finished(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');
        $scoring->addScore($match, TeamSide::TeamOne, 151);

        try {
            $scoring->addScore($match, TeamSide::TeamTwo, 10);
            $this->fail('Expected a finished round to reject more points.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('points', $exception->errors());
        }

        $round = $match->rounds()->first();
        $this->assertSame(151, $round->team_one_score);
        $this->assertSame(0, $round->team_two_score);
    }

    public function test_undo_reopens_a_winning_round_and_removes_the_win(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');
        $scoring->addScore($match, TeamSide::TeamTwo, 1);
        $scoring->addScore($match, TeamSide::TeamOne, 140);
        $scoring->addScore($match, TeamSide::TeamOne, 20);

        $round = $scoring->undoLatest($match);

        $this->assertSame(140, $round->team_one_score);
        $this->assertSame(RoundStatus::Playing, $round->status);
        $this->assertNull($round->winner);
        $this->assertNull($round->finished_at);
        $this->assertSame(0, $match->winsFor(TeamSide::TeamOne));
        $this->assertSame(2, $round->scores()->count());
    }

    public function test_undo_without_entries_fails(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $this->expectException(ValidationException::class);

        $scoring->undoLatest($match);
    }

    public function test_next_round_resets_scores_and_keeps_the_win_count(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');
        $scoring->addScore($match, TeamSide::TeamTwo, 160);

        $next = $scoring->startNextRound($match);
        $scoring->addScore($match, TeamSide::TeamTwo, 151);

        $this->assertSame(2, $next->round_number);
        $this->assertSame(0, $next->team_one_score);
        $this->assertSame(2, $match->rounds()->count());
        $this->assertSame(2, $match->winsFor(TeamSide::TeamTwo));
        $this->assertSame(0, $match->rounds()->orderByDesc('round_number')->first()->team_one_score);
    }

    public function test_next_round_is_rejected_while_the_round_is_open(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');
        $scoring->addScore($match, TeamSide::TeamOne, 30);

        $this->expectException(ValidationException::class);

        $scoring->startNextRound($match);
    }

    public function test_the_board_shows_the_winner_after_crossing_151(): void
    {
        $this->post(route('matches.store'), [
            'team_one_name' => 'الأحمر',
            'team_two_name' => 'الأزرق',
        ])->assertRedirect(route('home'));

        $match = DominoMatch::query()->firstOrFail();

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamTwo->value,
            'points' => 40,
        ])->assertRedirect(route('home'));

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 100,
        ])->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('الأحمر')
            ->assertSee('الأزرق')
            ->assertSee('100')
            ->assertSee('40');

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 51,
        ])->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('فاز')
            ->assertSee('151')
            ->assertSee('جولة جديدة');

        $this->post(route('rounds.store', $match))->assertRedirect(route('home'));

        $this->assertSame(1, $match->winsFor(TeamSide::TeamOne));
        $this->assertSame(2, $match->rounds()->count());
        $this->assertSame(0, $match->currentRound->team_one_score);
        $this->assertSame(RoundStatus::Playing, $match->currentRound->status);

        $this->delete(route('scores.undo', $match))->assertSessionHasErrors('score');

        $this->get(route('history'))
            ->assertOk()
            ->assertSee('الأحمر ضد الأزرق')
            ->assertSee('فاز الأحمر');
    }

    public function test_invalid_points_are_rejected(): void
    {
        $match = app(ScoringService::class)->startMatch($this->user, 'الأحمر', 'الأزرق');

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 0,
        ])->assertSessionHasErrors('points');

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 201,
        ])->assertSessionHasErrors('points');

        $this->assertSame(0, $match->rounds()->first()->team_one_score);
    }

    public function test_arabic_and_persian_digits_are_stored_as_english_points(): void
    {
        $match = app(ScoringService::class)->startMatch($this->user, 'الأحمر', 'الأزرق');

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => '٢٣',
        ])->assertRedirect(route('home'));

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamTwo->value,
            'points' => '۱۵',
        ])->assertRedirect(route('home'));

        $round = $match->rounds()->first();
        $this->assertSame(23, $round->team_one_score);
        $this->assertSame(15, $round->team_two_score);
    }

    public function test_letters_are_not_accepted_as_points(): void
    {
        $match = app(ScoringService::class)->startMatch($this->user, 'الأحمر', 'الأزرق');

        $this->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 'عشرون',
        ])->assertSessionHasErrors('points');

        $this->assertSame(0, $match->rounds()->first()->team_one_score);
    }

    public function test_reaching_100_while_the_other_team_is_zero_makes_them_mils_and_ends_the_round(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $round = $scoring->addScore($match, TeamSide::TeamOne, 100);

        $this->assertSame(RoundStatus::Finished, $round->status);
        $this->assertSame(TeamSide::TeamOne, $round->winner);
        $this->assertSame(TeamSide::TeamTwo, $round->mils_team);
        $this->assertSame(1, $match->winsFor(TeamSide::TeamOne));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ملص')
            ->assertSee('الأزرق')
            ->assertSee('فاز');
    }

    public function test_one_hundred_does_not_end_the_round_when_the_other_team_has_points(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');

        $scoring->addScore($match, TeamSide::TeamTwo, 5);
        $round = $scoring->addScore($match, TeamSide::TeamOne, 100);

        $this->assertSame(RoundStatus::Playing, $round->status);
        $this->assertNull($round->mils_team);
        $this->assertNull($round->winner);
    }

    public function test_undo_clears_a_mils_win(): void
    {
        $scoring = app(ScoringService::class);
        $match = $scoring->startMatch($this->user, 'الأحمر', 'الأزرق');
        $scoring->addScore($match, TeamSide::TeamOne, 100);

        $round = $scoring->undoLatest($match);

        $this->assertSame(0, $round->team_one_score);
        $this->assertSame(RoundStatus::Playing, $round->status);
        $this->assertNull($round->mils_team);
        $this->assertSame(0, $match->winsFor(TeamSide::TeamOne));
    }

    public function test_a_user_cannot_see_or_score_another_users_match(): void
    {
        $match = app(ScoringService::class)->startMatch($this->user, 'صقور', 'نسور');
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('home'))
            ->assertOk()
            ->assertDontSee('صقور');

        $this->actingAs($other)->get(route('history'))
            ->assertOk()
            ->assertDontSee('صقور');

        $this->actingAs($other)->post(route('scores.store', $match), [
            'team' => TeamSide::TeamOne->value,
            'points' => 10,
        ])->assertNotFound();

        $this->assertSame(0, $match->fresh()->rounds()->first()->team_one_score);
    }
}
