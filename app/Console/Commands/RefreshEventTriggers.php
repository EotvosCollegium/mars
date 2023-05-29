<?php

namespace App\Console\Commands;

use App\Models\EventTrigger;
use Illuminate\Console\Command;

class RefreshEventTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventtrigger:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh event trigger dates. For debug only.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (EventTrigger::all() as $event) {
            $event->update(['date' => $event->getTrigger()->nextDate()]);
            $this->info($event->name . ": ". $event->date);
            if($this->confirm('Run handle trigger?')) {
                $event->getTrigger()->handle();
            }
        }
        return 0;
    }
}
