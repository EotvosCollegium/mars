<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\User;
use App\Models\Transaction;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\Workshop;
use App\Models\WorkshopBalance;

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

        $skkt = \App\Models\Transaction::where(
            'payment_type_id',
            \App\Models\PaymentType::kkt()->id
        )
            ->where('semester_id', \App\Models\Semester::current()->id)
            ->sum('amount');

        \App\Models\WorkshopBalance::generateBalances(\App\Models\Semester::current()->id);
        $sum = \App\Models\WorkshopBalance::where('semester_id', \App\Models\Semester::current()->id)
            ->sum('allocated_balance');

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

    /**
     * Tests how workshop balances change
     * when a user pays kkt.
     * Does it both for externs and residents.
     */
    public function testPayment()
    {
        foreach ([true, false] as $is_extern) {
            $workshop_id = rand(1, count(Workshop::ALL));
            $workshop = Workshop::findOrFail($workshop_id);
            $user = User::factory()->create();

            if ($is_extern) {
                $user->setExtern();
            } else {
                $user->setCollegist(\App\Models\Role::RESIDENT);
            }

            $user->setStatusFor(Semester::current(), SemesterStatus::ACTIVE);  // important!

            $user->workshops()->attach($workshop_id);

            $with_same_status =
                $is_extern ? $workshop->externs() : $workshop->residents();
            $this->assertTrue(
                $with_same_status->filter(function (User $u) use ($user) {return $u->id == $user->id;})->count()
                  == 1
            );
            $this->assertTrue(is_null($user->payedKKT()));

            WorkshopBalance::generateBalances(Semester::current()->id);
            $balance = $workshop->balance();

            $old = $balance->allocated_balance;
            $user->payKKTNetreg(config('custom.kkt'), config('custom.netreg'));
            $balance->refresh();
            $new = $balance->allocated_balance;

            $this->assertTrue($user->payedKKT() == config('custom.kkt'));

            $this->assertTrue($new - $old == round(
                ($is_extern ? config('custom.workshop_balance_extern')
                                                           : config('custom.workshop_balance_resident'))
                                               * config('custom.kkt')
            ));
        }
    }
}
