<?php

namespace Database\Seeders;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\User;
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
            'has_long_answers' => false,
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
            'title' => "Curatorium members",
            'max_options' => 3,
            'has_long_answers' => false,
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
    }
}
