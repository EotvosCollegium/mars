<?php

namespace Tests\Unit;

use Tests\TestCase;

class EconomicTest extends TestCase
{
    /**
     * This test is to ensure that the sum of workshop balances
     * is between
     * config('custom.workshop_balance_resident)*skkt and
     * config('custom.workshop_balance_extern)*skkt
     * (where skkt is the entire income got from kkt).
     *
     * @return void
     */
    public function testWorkshopRatio()
    {
        // Should we generate users here?

        $transactions = \App\Models\Transaction::whereIn(
            'payment_type_id',
            [\App\Models\PaymentType::kkt()->id]
        )
            ->where('semester_id', \App\Models\Semester::current()->id)
            ->get();
        $skkt = $transactions->where('payment_type_id', \App\Models\PaymentType::kkt()->id)
            ->sum('amount');

        \App\Models\WorkshopBalance::generateBalances(\App\Models\Semester::current()->id);
        $sum = \App\Models\WorkshopBalance::all()->sum('allocated_balance');

        /*
        echo($skkt);
        echo("\n");
        echo($sum);
        echo("\n");
        echo(config('custom.workshop_balance_resident'));
        echo("\n");
        echo(config('custom.workshop_balance_extern'));
        echo("\n");
        */

        $diff1 = config('custom.workshop_balance_resident') * $skkt - $sum;
        $diff2 = config('custom.workshop_balance_extern') * $skkt - $sum;
        $this->assertTrue($diff1 * $diff2 <= 0);
    }
}
