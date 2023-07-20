<?php

namespace App\Providers;

use App\Listeners\MailGate;
use App\Models\FreePages;
use App\Models\PrintAccount;
use App\Models\SemesterStatus;
use App\Observers\FreePagesObserver;
use App\Observers\PrintAccountObserver;
use App\Observers\StatusObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MessageSending::class => [
            MailGate::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        SemesterStatus::observe(StatusObserver::class);
        FreePages::observe(FreePagesObserver::class);
        PrintAccount::observe(PrintAccountObserver::class);
    }
}
