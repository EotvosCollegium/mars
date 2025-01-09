<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use Illuminate\Support\Facades\App;
use App\Models\Semester;

class FinalizeSemesterEvaluation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalize-semester-evaluation {year} {part}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalizes the semester evaluation, deactivates collegists who has not set their new status.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year');
        $part = $this->argument('part');


        $semester = Semester::where('year', $year)->where('part', $part)->first();

        App::setLocale('hu');
        app(SemesterEvaluationController::class)->finalize($semester);
    }
}
