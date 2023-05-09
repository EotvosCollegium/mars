<?php

namespace Database\Seeders;

use App\Models\GeneralAssemblies\GeneralAssembly;
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
            'title' => "Today's general_assembly",
            'opened_at' => now(),
        ]);

        $openQuestion = $openSitting->questions()->create([
            'title' => "I support the election of the new Students' Council.",
            'max_options' => 1,
            'opened_at' => now()
        ]);
        $openQuestion->options()->create([
            'title' => "Yes",
            'votes' => 100
        ]);
        $openQuestion->options()->create([
            'title' => "No",
            'votes' => 12
        ]);
        $openQuestion->options()->create([
            'title' => "I abstain",
            'votes' => 9
        ]);

        $openCheckboxQuestion = $openSitting->questions()->create([
            'title' => "Curatorium members",
            'max_options' => 3,
            'opened_at' => now()
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "A",
            'votes' => 60
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "B",
            'votes' => 70
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "C",
            'votes' => 50
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "D",
            'votes' => 10
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "E",
            'votes' => 65
        ]);
        $openCheckboxQuestion->options()->create([
            'title' => "I abstain",
            'votes' => 5
        ]);
    }
}
