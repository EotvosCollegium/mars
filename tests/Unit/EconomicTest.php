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
 * Tests for the feature tracking kkt payments and workshop balances.
 */
class EconomicTest extends TestCase
{
    // We use constants for now as kkt and netreg values.
    public const TEST_KKT = 2500;
    public const TEST_NETREG = 500;
    // The threshold difference under which we accept the equality.
    public const THRESHOLD_DIFF = 1.5;

    /**
     * Tests how workshop balances change
     * when a user pays kkt.
     * Does it for externs or residents;
     * depending on the parameter.
     * The generated test user can also be a member of more than one workshops.
     *
     * @param bool $is_extern
     * @param int $number_of_workshops
     * @return void
     */
    private function testPayment(bool $is_extern, int $number_of_workshops = 1): void
    {
        $workshops = Workshop::all()->random($number_of_workshops)->all();
        $user = User::factory()->create();

        if ($is_extern) {
            $user->setExtern();
        } else {
            $user->setResident();
        }

        $user->setStatusFor(Semester::current(), SemesterStatus::ACTIVE);  // important!
        foreach($workshops as $workshop) {
            $user->workshops()->attach($workshop);
        }

        $this->assertTrue(is_null($user->payedKKT()));
        WorkshopBalance::generateBalances(Semester::current()); // this also ensures the balance won't be null

        $balances = array_map(function ($workshop) {return $workshop->balance();}, $workshops);
        // the old allocated balances
        $olds = array_map(function ($balance) {return $balance->allocated_balance;}, $balances);

        // we give the payer's id as the receiver's id
        // because Checkout::studentsCouncil()->handler_id returns null
        $user->payKKTNetreg($user->id, self::TEST_KKT, self::TEST_NETREG);

        $this->assertTrue($user->payedKKT() == self::TEST_KKT);

        $news = array_map(function ($balance) {return $balance->fresh()->allocated_balance;}, $balances);

        $amount_for_workshops =
            ($is_extern ? config('custom.workshop_balance_extern')
                        : config('custom.workshop_balance_resident'))
                * self::TEST_KKT;
        for ($i = 0; $i < $number_of_workshops; ++$i) {
            // to avoid floating point equality checking
            // and to allow some error from rounding:
            $this->assertTrue(abs(
                $number_of_workshops * ($news[$i] - $olds[$i])
                                - $amount_for_workshops
            ) < self::THRESHOLD_DIFF);
        }
    }

    /**
     * The concrete call of testPayment for externs with one workshop.
     */
    public function testExternPaymentWithOneWorkshop()
    {
        $this->testPayment(true);
    }

    /**
     * The concrete call of testPayment for residents with one workshop.
     */
    public function testResidentPaymentWithOneWorkshop()
    {
        $this->testPayment(false);
    }

    /**
     * The concrete call of testPayment for externs with two workshops.
     */
    public function testExternPaymentWithTwoWorkshops()
    {
        $this->testPayment(true, 2);
    }

    /**
     * The concrete call of testPayment for residents with two workshops.
     */
    public function testResidentPaymentWithTwoWorkshops()
    {
        $this->testPayment(false, 2);
    }
}
