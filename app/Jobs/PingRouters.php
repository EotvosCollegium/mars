<?php

namespace App\Jobs;

use App\Console\Commands;
use App\Models\Internet\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PingRouters implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Router::all() as $router) {
            $result = Commands::pingRouter($router);
            if ($result == '') {
                $router->update([
                    'failed_for' => 0,
                ]);
            } elseif (config('app.debug') == false) {
                $router->increment('failed_for');
                $router->sendWarning();
            }
        }
    }
}
