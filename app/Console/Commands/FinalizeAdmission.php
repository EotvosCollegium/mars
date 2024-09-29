<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AdmissionController;
use App\Models\User;
use Illuminate\Support\Facades\App;

class FinalizeAdmission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalize-admission {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalizes the admission process.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Auth::login(User::whereEmail($this->argument('email'))->first());
        App::setLocale('hu');
        app(AdmissionController::class)->finalize();
    }
}
