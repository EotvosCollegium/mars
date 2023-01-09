<?php

namespace App\Observers;

use App\Models\FreePages;
use Illuminate\Support\Facades\DB;

class FreePagesObserver
{
    /**
     * Handle the FreePages "created" event.
     *
     * @param  \App\Models\FreePages  $freePages
     * @return void
     */
    public function created(FreePages $freePages)
    {
        DB::table('print_account_history')->insert([
            'user_id' => $freePages->user_id,
            'balance_change' => 0,
            'free_page_change' => $freePages->amount,
            'deadline_change' => $freePages->deadline,
            'modified_by' => $freePages->last_modified_by,
            'modified_at' => $freePages->updated_at,
        ]);
    }

    /**
     * Handle the FreePages "updated" event.
     *
     * @param  \App\Models\FreePages  $freePages
     * @return void
     */
    public function updated(FreePages $freePages)
    {
        $newDeadline = $freePages->isDirty('deadline') ? $freePages->deadline : null;

        DB::table('print_account_history')->insert([
            'user_id' => $freePages->user_id,
            'balance_change' => 0,
            'free_page_change' => $freePages->amount,
            'deadline_change' => $newDeadline,
            'modified_by' => $freePages->last_modified_by,
            'modified_at' => $freePages->updated_at,
        ]);
    }

    /**
     * Handle the FreePages "deleted" event.
     *
     * @param  \App\Models\FreePages  $freePages
     * @return void
     */
    public function deleted(FreePages $freePages)
    {
        //
    }

    /**
     * Handle the FreePages "restored" event.
     *
     * @param  \App\Models\FreePages  $freePages
     * @return void
     */
    public function restored(FreePages $freePages)
    {
        //
    }

    /**
     * Handle the FreePages "force deleted" event.
     *
     * @param  \App\Models\FreePages  $freePages
     * @return void
     */
    public function forceDeleted(FreePages $freePages)
    {
        //
    }
}
