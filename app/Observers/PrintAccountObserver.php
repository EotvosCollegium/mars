<?php

namespace App\Observers;

use App\Models\PrintAccount;
use Illuminate\Support\Facades\DB;

class PrintAccountObserver
{
    /**
     * Handle the PrintAccount "created" event.
     *
     * @param  \App\Models\PrintAccount  $printAccount
     * @return void
     */
    public function created(PrintAccount $printAccount)
    {

    }

    /**
     * Handle the PrintAccount "updated" event.
     *
     * @param  \App\Models\PrintAccount  $printAccount
     * @return void
     */
    public function updated(PrintAccount $printAccount)
    {
        if ($printAccount->isDirty('balance')) {
            DB::table('print_account_history')->insert([
                'user_id' => $printAccount->user_id,
                'balance_change' => $printAccount->balance - $printAccount->getOriginal('balance'),
                'free_page_change' => 0,
                'deadline_change' => null,
                'modified_by' => $printAccount->last_modified_by,
                'modified_at' => $printAccount->modified_at
            ]);
        }
    }

    /**
     * Handle the PrintAccount "deleted" event.
     *
     * @param  \App\Models\PrintAccount  $printAccount
     * @return void
     */
    public function deleted(PrintAccount $printAccount)
    {
        //
    }

    /**
     * Handle the PrintAccount "restored" event.
     *
     * @param  \App\Models\PrintAccount  $printAccount
     * @return void
     */
    public function restored(PrintAccount $printAccount)
    {
        //
    }

    /**
     * Handle the PrintAccount "force deleted" event.
     *
     * @param  \App\Models\PrintAccount  $printAccount
     * @return void
     */
    public function forceDeleted(PrintAccount $printAccount)
    {
        //
    }
}
