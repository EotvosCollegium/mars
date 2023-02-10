<?php

namespace Tests\Unit;

use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Voting\Question;
use App\Models\Voting\Sitting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test sitting votings
 *
 * @return void
 */
class VotingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_voting_on_closed_question()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->subDay()]);

        $this->expectException(\Exception::class);
        $question->vote($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_on_not_opened_question()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => null, 'closed_at' => null]);

        $this->expectException(\Exception::class);
        $question->vote($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_twice()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay()]);

        $this->expectException(\Exception::class);
        $question->vote($user, [$question->options->first()]);
        $question->vote($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_radio()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 1]);

        $question->vote($user, [$question->options->first()]);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(0, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);
    }

    /**
     * @return void
     */
    public function test_voting_radio_with_more_options()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 1]);

        $this->expectException(\Exception::class);
        $question->vote($user, [$question->options->first(), $question->options->get(1)]);
    }

    /**
     * @return void
     */
    public function test_voting_checkbox()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 3]);

        $question->vote($user, [$question->options->first(), $question->options->get(1)]);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(1, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);
    }

    /**
     * @return void
     */
    public function test_voting_checkbox_with_more_options()
    {
        $user = User::factory()->create();
        $user->setExtern();
        $user->setStatus(SemesterStatus::ACTIVE);

        $sitting = Sitting::factory()->create();
        $question = Question::factory()
            ->for($sitting)
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 2]);

        $this->expectException(\Exception::class);
        $question->vote($user, [$question->options->first(), $question->options->get(1), $question->options->get(2)]);
    }
}
