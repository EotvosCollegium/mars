<?php

namespace App\Observers;

use App\Models\FreePrintingCredits;
use Illuminate\Support\Facades\DB;

class FreePrintingCreditsObserver
{
    /**
     * Handle the FreePrintingCredits "created" event.
     *
     * @param  \App\Models\FreePrintingCredits  $freePrintingCredits
     * @return void
     */
    public function created(FreePrintingCredits $freePrintingCredits)
    {
        DB::table('print_account_history')->insert([
            'user_id' => $freePrintingCredits->user_id,
            'balance_change' => 0,
            'free_printing_credits_change' => $freePrintingCredits->amount,
            'deadline_change' => $freePrintingCredits->deadline,
            'modified_by' => $freePrintingCredits->last_modified_by,
            'modified_at' => $freePrintingCredits->updated_at,
        ]);
    }

    /**
     * Handle the FreePrintingCredits "updated" event.
     *
     * @param  \App\Models\FreePrintingCredits  $freePrintingCredits
     * @return void
     */
    public function updated(FreePrintingCredits $freePrintingCredits)
    {
        $newDeadline = $freePrintingCredits->isDirty('deadline') ? $freePrintingCredits->deadline : null;

        DB::table('print_account_history')->insert([
            'user_id' => $freePrintingCredits->user_id,
            'balance_change' => 0,
            'free_printing_credits_change' => $freePrintingCredits->amount,
            'deadline_change' => $newDeadline,
            'modified_by' => $freePrintingCredits->last_modified_by,
            'modified_at' => $freePrintingCredits->updated_at,
        ]);
    }

    /**
     * Handle the FreePrintingCredits "deleted" event.
     *
     * @param  \App\Models\FreePrintingCredits  $freePrintingCredits
     * @return void
     */
    public function deleted(FreePrintingCredits $freePrintingCredits)
    {
        //
    }

    /**
     * Handle the FreePrintingCredits "restored" event.
     *
     * @param  \App\Models\FreePrintingCredits  $freePrintingCredits
     * @return void
     */
    public function restored(FreePrintingCredits $freePrintingCredits)
    {
        //
    }

    /**
     * Handle the FreePrintingCredits "force deleted" event.
     *
     * @param  \App\Models\FreePrintingCredits  $freePrintingCredits
     * @return void
     */
    public function forceDeleted(FreePrintingCredits $freePrintingCredits)
    {
        //
    }
}
