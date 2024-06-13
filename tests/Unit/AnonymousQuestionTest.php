<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;

use Tests\TestCase;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Semester;
use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Question;

/**
 * Tests anonymous feedback questions.
 * Mostly based on GeneralAssemblyTest.
 */
class AnonymousQuestionTest extends TestCase
{
    /**
     * Sets the start and end dates of a given semester's evaluation form.
     */
    private function setDates(Semester $semester, Carbon $startDate, Carbon $endDate): void
    {
        $admin = User::factory()->create(['verified' => true]);
        $admin->roles()->attach(\App\Models\Role::sysAdmin());

        $response = $this->actingAs($admin)->post('/secretariat/evaluation/period', [
            'semester_id' => $semester->id,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * Open the current semester's evaluation form;
     * with start and end dates close to the current time.
     */
    private function openForm(): void
    {
        $this->setDates(
            Semester::current(),
            Carbon::now()->subMinute(1),
            Carbon::now()->addMinute(20)
        );
    }

    /**
     * Close the current semester's evaluation form
     * by giving a start date after the current time.
     */
    private function delayForm(): void
    {
        $this->setDates(
            Semester::current(),
            Carbon::now()->addMinute(10),
            Carbon::now()->addMinute(11)
        );
    }

    /**
     * Tests answering a question belonging to the current semester
     * but before the start date of the evaluation form
     * (which should fail).
     * @return void
     */
    public function test_answering_not_yet_opened_question(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create();

        $this->delayForm(); // important!
        // don't forget this
        $question->refresh();

        $this->expectExceptionMessage("Tried to store answers for a question which is not open");
        $question->storeAnswers(
            $user,
            $question->options->first(),
            AnswerSheet::createForUser($user, $semester)
        );
    }

    /**
     * Tests answering the same question twice as the same user
     * (which should fail).
     * @return void
     */
    public function test_answering_twice(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current(); // gets created if does not already exist
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['max_options' => 2]);

        $this->openForm(); // this also sets the start and end dates
        $question->refresh();

        $this->expectExceptionMessage("The user has already answered this question");

        $answerSheet1 = AnswerSheet::createForUser($user, $semester);
        $answerSheet2 = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers($user, $question->options->random(2)->all(), $answerSheet1);
        $question->storeAnswers($user, $question->options->random(), $answerSheet2);
    }

    /**
     * Tests answering a single-choice question.
     * @return void
     */
    public function test_answering_radio(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['max_options' => 1]);

        $this->openForm();
        $question->refresh();

        $answerSheet = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers($user, $question->options->first(), $answerSheet);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(0, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);

        // For some reason, it seems to be stable
        // only if we manipulate the database directly.
        $this->assertTrue(
            DB::table('question_user')
                ->where('question_id', $question->id)->where('user_id', $user->id)
                ->exists()
        );

        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->first()->id)
                ->exists()
        );
        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->get(1)->id)
                ->doesntExist()
        );
        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->get(2)->id)
                ->doesntExist()
        );
    }

    /**
     * Tests choosing more options
     * for a single-choice question
     * (which should fail).
     * @return void
     */
    public function test_answering_radio_with_more_options(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['max_options' => 1]);

        $this->openForm();
        $question->refresh();

        $this->expectExceptionMessage("More answers given then allowed ({$question->max_options})");
        $question->storeAnswers(
            $user,
            $question->options->random(2)->all(),
            AnswerSheet::createForUser($user, $semester)
        );
    }

    /**
     * Tests answering a multiple-choice question.
     * @return void
     */
    public function test_answering_checkbox(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(4)
            ->create(['max_options' => 4]);

        $this->openForm();
        $question->refresh();

        $answerSheet = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers(
            $user,
            [$question->options->first(), $question->options->get(1)],
            $answerSheet
        );

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(1, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);
        $this->assertEquals(0, $question->options->get(3)->votes);

        $this->assertTrue(
            DB::table('question_user')
                ->where('question_id', $question->id)->where('user_id', $user->id)
                ->exists()
        );

        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->first()->id)
                ->exists()
        );
        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->get(1)->id)
                ->exists()
        );
        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->get(2)->id)
                ->doesntExist()
        );
        $this->assertTrue(
            $answerSheet->chosenOptions()
                ->where('id', $question->options->get(3)->id)
                ->doesntExist()
        );
    }

    /**
     * Tests giving more options than allowed
     * for a multiple-choice question
     * (which should fail).
     * @return void
     */
    public function test_answering_checkbox_with_more_options(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(4)
            ->create(['max_options' => 2]);

        $this->openForm();
        $question->refresh();

        $this->expectExceptionMessage("More answers given then allowed ({$question->max_options})");
        $question->storeAnswers($user, [$question->options->first(), $question->options->get(1), $question->options->get(2)]);
    }

    /**
     * Tests answering a question with a long textual answer.
     * @return void
     */
    public function test_answering_long_text(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->create(['has_long_answers' => true]);

        $this->openForm();
        $question->refresh();

        $answerSheet = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers($user, "The quick brown fox jumped over the lazy dog.", $answerSheet);

        $this->assertTrue(
            DB::table('question_user')
                ->where('question_id', $question->id)->where('user_id', $user->id)
                ->exists()
        );

        $this->assertTrue(
            $answerSheet->longAnswers()
                ->where('question_id', $question->id)
                ->exists()
        );
    }

    /**
     * Tests answering a question with a long textual answer
     * when it does not support that
     * (this should fail).
     * @return void
     */
    public function test_answering_long_text_when_not_supported(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->create(['has_long_answers' => false]);

        $this->openForm();
        $question->refresh();

        $answerSheet = AnswerSheet::createForUser($user, $semester);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, "The quick brown fox jumped over the lazy dog.", $answerSheet);
    }
}
