<?php

namespace Tests\Unit;

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
     * Open the current semester's evaluation form;
     * with start and end dates close to the current time.
     */
    public static function openForm(): void
    {
        app(\App\Http\Controllers\Secretariat\SemesterEvaluationController::class)
            ->updatePeriodicEvent(
                Semester::current(),
                Carbon::now()->subMinute(1),
                Carbon::now()->addMinute(20)
            );
    }

    /**
     * Close the current semester's evaluation form
     * by giving a start date after the current time.
     */
    public static function delayForm(): void
    {
        app(\App\Http\Controllers\Secretariat\SemesterEvaluationController::class)
            ->updatePeriodicEvent(
                Semester::current(),
                Carbon::now()->addMinute(10),
                Carbon::now()->addMinute(11)
            );
    }


    /**
     * Tests answering a question belonging to an already closed semester
     * (which should fail).
     * @return void
     */
    public function test_answering_closed_question(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        $semester = Semester::previous(); // important!
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null]);

        $this->expectException(\Exception::class);
        $question->storeAnswers(
            $user,
            $question->options->first(),
            AnswerSheet::createForUser($user, $semester)
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

        self::delayForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null]);

        $this->expectException(\Exception::class);
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

        self::openForm();
        $semester = Semester::current(); // gets created if does not already exist
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null]);

        $this->expectException(\Exception::class);

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

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'max_options' => 1]);

        $answerSheet = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers($user, $question->options->first(), $answerSheet);

        $this->assertEquals(1, $question->options->first()->votes);
        $this->assertEquals(0, $question->options->get(1)->votes);
        $this->assertEquals(0, $question->options->get(2)->votes);

        $this->assertTrue(
            $question->users()
                ->where('id', $user->id)
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

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(3)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'max_options' => 1]);

        $this->expectException(\Exception::class);
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
    public function test_voting_checkbox(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(4)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'max_options' => 4]);

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
            $question->users()
                ->where('id', $user->id)
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
    public function test_voting_checkbox_with_more_options(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->hasOptions(4)
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'max_options' => 2]);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, [$question->options->first(), $question->options->get(1), $question->options->get(2)]);
    }

    /**
     * Tests answering a question with a long textual answer.
     * @return void
     */
    public function test_answering_long_text(): void
    {
        $user = User::factory()->hasEducationalInformation()->create();

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'has_long_answers' => true]);

        $answerSheet = AnswerSheet::createForUser($user, $semester);
        $question->storeAnswers($user, "The quick brown fox jumped over the lazy dog.", $answerSheet);

        $this->assertTrue(
            $question->users()
                ->where('id', $user->id)
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

        self::openForm();
        $semester = Semester::current();
        $question = Question::factory()
            ->for($semester, 'parent')
            ->create(['opened_at' => now()->subDay(), 'closed_at' => null, 'has_long_answers' => false]);

        $answerSheet = AnswerSheet::createForUser($user, $semester);

        $this->expectException(\Exception::class);
        $question->storeAnswers($user, "The quick brown fox jumped over the lazy dog.", $answerSheet);
    }
}
