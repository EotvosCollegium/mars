<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Jobs\PeriodicEventsProcessor;

class ProcessPeriodicEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-periodic-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually trigger periodic event processor.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new PeriodicEventsProcessor())->handle();
    }
}
