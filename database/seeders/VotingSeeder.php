<?php

namespace Database\Seeders;

use App\Models\Sitting;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Seeder;

class VotingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sitting::factory()->count(3)->create();
        $openSitting = Sitting::create([
            'title' => "Today's sitting",
            'opened_at' => now(),
        ]);

        Question::factory()->count(15)->create();
        $openQuestion = Question::create([
            'sitting_id' => $openSitting->id,
            'title' => "I support the election of the new Students' Council.",
            'opened_at' => now(),
        ]);

        $yesOption = Option::create([
            'question_id' => $openQuestion->id,
            'title' => "Yes"
        ]);
        $noOption = Option::create([
            'question_id' => $openQuestion->id,
            'title' => "No"
        ]);
        $abstainOption = Option::create([
            'question_id' => $openQuestion->id,
            'title' => "Abstain"
        ]);
    }
}
