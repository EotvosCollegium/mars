<?php

namespace App\Observers;

use App\Models\SemesterStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StatusObserver
{
    /**
     * Handle the SemesterStatus "created" event.
     *
     * @param  \App\Models\SemesterStatus  $semesterStatus
     * @return void
     */
    public function created(SemesterStatus $semesterStatus)
    {
        Mail::to($semesterStatus->user)->queue(new \App\Mail\StatusUpdated($semesterStatus));
    }

    /**
     * Handle the SemesterStatus "updated" event.
     *
     * @param  \App\Models\SemesterStatus  $semesterStatus
     * @return void
     */
    public function updated(SemesterStatus $semesterStatus)
    {
        Mail::to($semesterStatus->user)->queue(new \App\Mail\StatusUpdated($semesterStatus));

    }

    /**
     * Handle the SemesterStatus "deleted" event.
     *
     * @param  \App\Models\SemesterStatus  $semesterStatus
     * @return void
     */
    public function deleted(SemesterStatus $semesterStatus)
    {
        //
    }

    /**
     * Handle the SemesterStatus "restored" event.
     *
     * @param  \App\Models\SemesterStatus  $semesterStatus
     * @return void
     */
    public function restored(SemesterStatus $semesterStatus)
    {
        //
    }

    /**
     * Handle the SemesterStatus "force deleted" event.
     *
     * @param  \App\Models\SemesterStatus  $semesterStatus
     * @return void
     */
    public function forceDeleted(SemesterStatus $semesterStatus)
    {
        //
    }
}
