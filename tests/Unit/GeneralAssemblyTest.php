<?php

namespace Tests\Unit;

use App\Models\EducationalInformation;
use App\Models\SemesterStatus;
use App\Models\User;
use App\Models\Question;
use App\Models\GeneralAssemblies\GeneralAssembly;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test general_assembly votings
 *
 * @return void
 */
class GeneralAssemblyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_voting_on_closed_question()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->subDay()]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_on_not_opened_question()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => null, 'closed_at' => null]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_twice()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay()]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first()]);
        $question->storeAnswers($user, [$question->options->first()]);
    }

    /**
     * @return void
     */
    public function test_voting_radio()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 1]);

        $question->storeAnswers($user, [$question->options->first()]);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(0, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);

        $this->assertTrue(
            $question->users()
                ->where('id', $user->id)
                ->exists()
        );
    }

    /**
     * @return void
     */
    public function test_voting_radio_with_more_options()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 1]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first(), $question->options->get(1)]);
    }

    /**
     * @return void
     */
    public function test_voting_checkbox()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 3]);

        $question->storeAnswers($user, [$question->options->first(), $question->options->get(1)]);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(1, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);

        $this->assertTrue(
            $question->users()
                ->where('id', $user->id)
                ->exists()
        );
    }

    /**
     * @return void
     */
    public function test_voting_checkbox_with_more_options()
    {
        $user = User::factory()->create();

        $general_assembly = GeneralAssembly::factory()->create();
        $question = Question::factory()
            ->for($general_assembly, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => now()->addDay(), 'max_options' => 2]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first(), $question->options->get(1), $question->options->get(2)]);
    }

    /**
     * @return void
     */
    public function test_passive_users_get_excused_automatically(): void
    {
        /** @var User $userActive */
        /** @var User $userPassive1 */
        /** @var User $userPassive2 */
        $userActive = User::factory()->create();
        $userPassive1 = User::factory()->create();
        $userPassive2 = User::factory()->create();
        $userActive->setStatus(SemesterStatus::ACTIVE);
        $userPassive1->setStatus(SemesterStatus::PASSIVE);
        $userPassive2->setStatus(SemesterStatus::PASSIVE);

        /** @var GeneralAssembly $generalAssembly */
        $generalAssembly = GeneralAssembly::factory()->create();
        $excused = $generalAssembly->excusedUsers()->get();
        $this->assertEquals(2, $excused->count());
        $this->assertTrue($excused->contains($userPassive1) && $excused->contains($userPassive2));
        $this->assertNotNull($excused->first()->pivot->comment); // Check if excuse reason is set
    }

    /**
     * @return void
     */
    public function test_new_students_pass_requirements(): void
    {
        $user = User::factory()->create(['verified' => true]);
        EducationalInformation::factory()->create(['user_id' => $user->id, 'year_of_acceptance' => now()->year]);

        $generalAssembly = GeneralAssembly::factory()->create(['closed_at' => Carbon::createFromDate(now()->year, 2, 15)]);
        $generalAssembly2 = GeneralAssembly::factory()->create(['closed_at' => Carbon::createFromDate(now()->year, 9, 15)]);

        $generalAssembly->presenceChecks()->create();
        $generalAssembly2->presenceChecks()->create();

        $this->assertTrue(GeneralAssembly::requirementsPassed($user));

        $generalAssembly->update(['closed_at' => now()->setYear(now()->year)->setMonth(9)->setDay(16)]);
        $this->assertFalse(GeneralAssembly::requirementsPassed($user));
    }
}
