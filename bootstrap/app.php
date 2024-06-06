<?php

use App\Http\Middleware\Locale;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\NotifyAboutEvaluation;
use App\Http\Middleware\RedirectTenantsToUpdate;
use App\Jobs\PeriodicEventsProcessor;
use App\Jobs\PingRouters;
use App\Jobs\ProcessWifiConnections;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        // commands: __DIR__ . '/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/login');
        $middleware->trustProxies(
            at: '172.16.9.200',
            headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
        $middleware->web(append: [
            Locale::class,
            LogRequests::class,
            NotifyAboutEvaluation::class,
            RedirectTenantsToUpdate::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new PeriodicEventsProcessor())->daily()->at('13:00')->onFailure(function () {
            Log::error('Error processing periodic events.');
        });
        $schedule->job(new PingRouters())->everyFiveMinutes()->onFailure(function () {
            Log::error('Error pinging routers');
        });
        $schedule->job(new ProcessWifiConnections())->dailyAt('01:00')->onFailure(function () {
            Log::error('Error processing wifi connections.');
        });
        ;

        $schedule->command('backup:clean')->daily()->at('01:00')->onFailure(function () {
            Log::error('Error cleaning the a backup.');
        });
        $schedule->command('backup:run --only-db')->daily()->at('01:30')->onFailure(function () {
            Log::error('Error creating a backup.');
        });
    })
    ->create();
