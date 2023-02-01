<?php

namespace Database\Seeders;

use App\Models\Sitting;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class VotingSeeder extends Seeder
{

    /**Generate a sitting with questions too. */
    /*
    public static function createFakeSitting()
    {
        $faker = Faker\Factory::create('en_UK');
        $sitting = Sitting::create([
            'title' => $faker->realText($maxNbChars = 20),
            'opened_at' => now()->addHours($faker->numberBetween(-3, -2)),
            'closed_at' => now()->addHours($faker->numberBetween(-1, 0)),
        ]);
        for ($i=0; $i<3; $i++) {
            $opened_at = $sitting->closed_at->addMinutes($faker->numberBetween(-50, -30));//$this->faker->dateTime($min = $sitting->opened_at, $max=$sitting->closed_at);
            $question = $sitting->addQuestion(
                $faker->realText($maxNbChars = 20),
                $faker->numberBetween(1, 3),
                $opened_at,
                $opened_at->addMinutes($faker->numberBetween(5, 10))
            );
            //$question->votes=numberBetween(0, 100); $question->save();
            //TODO: faking of question_user table
        }
    }
    */

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //for ($i=0; $i<5; $i++) VotingSeeder::createFakeSitting();

        $openSitting = Sitting::create([
            'title' => "Today's sitting",
            'opened_at' => now(),
        ]);

        $openQuestion = $openSitting->addQuestion(
            "I support the election of the new Students' Council.",
            1,
            now()
        );
        $openQuestion->addOption("Yes");
        $openQuestion->addOption("No");
        $openQuestion->addOption("I abstain");

        $openCheckboxQuestion = $openSitting->addQuestion(
            "Curatorium members",
            3,
            now()
        );
        $openQuestion->addOption("A");
        $openQuestion->addOption("B");
        $openQuestion->addOption("C");
        $openQuestion->addOption("D");
        $openQuestion->addOption("E");
        $openQuestion->addOption("I abstain");
    }
}
