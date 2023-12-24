<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Checkout;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\Workshop;
use App\Models\WorkshopBalance;

/**
 * Tests for the function tracking kkt payments and workshop balances.
 */
class EconomicTest extends TestCase
{
    // We use constants for now as kkt and netreg values.
    public const TEST_KKT = 2500;
    public const TEST_NETREG = 500;

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

        \App\Models\WorkshopBalance::generateBalances(\App\Models\Semester::current());
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
     * Does it for externs or residents;
     * depending on the parameter.
     */
    private function testPayment(bool $is_extern)
    {
        foreach ([true, false] as $is_extern) {
            $workshop = Workshop::all()->random();
            $workshop_id = $workshop->id;
            $user = User::factory()->create();

            if ($is_extern) {
                $user->setExtern();
            } else {
                $user->setResident();
            }

            $user->setStatusFor(Semester::current(), SemesterStatus::ACTIVE);  // important!

            $user->workshops()->attach($workshop);

            $this->assertTrue(is_null($user->payedKKT()));

            WorkshopBalance::generateBalances(Semester::current()); // this ensures the balance won't be null
            $balance = $workshop->balance();

            $old = $balance->allocated_balance;
            // we give the payer's id as the receiver's id
            // because Checkout::studentsCouncil()->handler_id returns null
            $user->payKKTNetreg($user->id, self::TEST_KKT, self::TEST_NETREG);
            $balance->refresh();
            $new = $balance->allocated_balance;

            $this->assertTrue($user->payedKKT() == self::TEST_KKT);

            $this->assertTrue($new - $old == round(
                ($is_extern ? config('custom.workshop_balance_extern')
                            : config('custom.workshop_balance_resident'))
                    * self::TEST_KKT
            ));
        }
    }

    /**
     * The concrete call of testPayment for externs.
     */
    public function testExternPayment()
    {
        $this->testPayment(true);
    }

    /**
     * The concrete call of testPayment for residents.
     */
    public function testResidentPayment()
    {
        $this->testPayment(false);
    }
}
