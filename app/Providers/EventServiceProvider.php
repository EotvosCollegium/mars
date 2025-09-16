<?php

namespace App\Providers;

use App\Listeners\MailGate;
use App\Models\FreePrintingCredits;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\LanguageExam;
use App\Models\PrintAccount;
use App\Models\RoleUser;
use App\Models\SemesterStatus;
use App\Observers\FreePrintingCreditsObserver;
use App\Observers\GeneralAssemblyObserver;
use App\Observers\LanguageExamObserver;
use App\Observers\PrintAccountObserver;
use App\Observers\RoleUserObserver;
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
        FreePrintingCredits::observe(FreePrintingCreditsObserver::class);
        PrintAccount::observe(PrintAccountObserver::class);
        RoleUser::observe(RoleUserObserver::class);
        GeneralAssembly::observe(GeneralAssemblyObserver::class);
        LanguageExam::observe(LanguageExamObserver::class);
    }
}
