<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Semester;
use App\Models\User;
use App\Models\Role;
use App\Models\GeneralAssemblies\Question;

class AnonymousQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // for generating long answers
        $faker = \Faker\Factory::create();

        foreach(Semester::all()->filter(
                // not for future semesters
                function (Semester $semester) {return $semester->isCurrent() || $semester->isClosed();})
                as $semester) {
            $singleChoice = Question::factory()
                ->for($semester, 'parent')
                ->hasOptions(4)
                ->create(['opened_at' => now(), 'closed_at' => null, 'max_options' => 1]);
            $multipleChoice = Question::factory()
                ->for($semester, 'parent')
                ->hasOptions(3)
                ->create(['opened_at' => now(), 'closed_at' => null, 'max_options' => 3]);
            $withLongAnswers = Question::factory()
                ->for($semester, 'parent')
                ->create(['opened_at' => now(), 'closed_at' => null, 'max_options' => 0, 'has_long_answers' => true]);

            // the test users should not be included
            foreach(User::withRole(Role::COLLEGIST)->where('id', '>', 4)->get() as $collegist) {
                $answerSheet = AnswerSheet::createForUser($collegist, $semester);

                $singleChoice->giveAnonymousAnswer($collegist, $answerSheet, $singleChoice->options->random());
                $multipleChoice->giveAnonymousAnswer($collegist, $answerSheet, $multipleChoice->options->random(2)->all());
                $withLongAnswers->giveLongAnswer($collegist, $answerSheet, $faker->text());
            }
        }
    }
}
