<?php

namespace Database\Seeders;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\User;
use App\Models\Question;
use Illuminate\Database\Seeder;

class GeneralAssemblySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $openSitting = GeneralAssembly::create([
            'title' => "Today's general assembly",
            'opened_at' => now(),
        ]);

        $openQuestion = $openSitting->questions()->create([
            'title' => "I support the election of the new Students' Council.",
            'max_options' => 1,
            'question_type' => Question::SELECTION,
            'opened_at' => now()
        ]);
        $openQuestion->options()->create([
            'title' => "Yes",
            'votes' => 0
        ]);
        $openQuestion->options()->create([
            'title' => "No",
        ]);
        $openQuestion->options()->create([
            'title' => "I abstain",
        ]);

        $openCheckboxQuestion = $openSitting->questions()->create([
            'title' => "Curatorium members (multiple choice)",
            'max_options' => 3,
            'question_type' => Question::SELECTION,
            'opened_at' => now()
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "A",
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "B",
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "C",
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "D",
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "E",
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "I abstain",
        ]);

        foreach(User::collegists() as $collegist) {
            $openQuestion->storeAnswers($collegist, [$openQuestion->options->random()]);
            $openCheckboxQuestion->storeAnswers($collegist, $openCheckboxQuestion->options->random(2)->all());
        }

        $openRankingQuestion = $openSitting->questions()->create([
            'title' => "Curatorium members (STV)",
            'max_options' => 5,
            'question_type' => Question::RANKING,
            'opened_at' => now()
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Űrlaki Anna Őzike",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Peter Venkman",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Ray Stantz",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Egon Spengler",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Winston Zeddemore",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Dana Barrett",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Louis Tully",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Janine Melnitz",
        ]);
        $openRankingQuestion->options()->create([
            'title' => "Walter Peck",
        ]);
        $rankingOptionIds = $openRankingQuestion->options()->pluck('id')->all();

        // generating random ranking votes
        $rankingVoteCount = rand(0, 300);
        for ($i = 0; $i < $rankingVoteCount; ++$i) {
            shuffle($rankingOptionIds);
            $orderedOptionCount = rand(0, count($rankingOptionIds) - 1);
            $answer = array_slice($rankingOptionIds, 0, $orderedOptionCount);
            // we won't connect them to users,
            // just write them among the answers
            $openRankingQuestion->longAnswers()->create([
                'text' => json_encode($answer)
            ]);
        }
    }
}
