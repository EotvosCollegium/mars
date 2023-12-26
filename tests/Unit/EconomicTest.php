<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Checkout;
use App\Models\Role;
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
    // We use constants for now as kkt and netreg values and ratios.
    public const TEST_KKT = 2500;
    public const TEST_NETREG = 500;
    public const TEST_RATIO_RESIDENT = 0.5;
    public const TEST_RATIO_EXTERN = 0.6;

    /**
     * Tests how workshop balances change
     * when a user pays kkt.
     * Does it for a user with an active status,
     * who can be a resident or an extern
     * and can have one or multiple workshops.
     * 
     * In the test cases,
     * we provide users with
     * these parameters varied.
     *
     * @param User $user
     * @return void
     */
    private function testPayment(User $user): void
    {
        $workshops = $user->workshops;

        // this also ensures the balance won't be null
        WorkshopBalance::generateBalances(Semester::current(),
                                          self::TEST_RATIO_RESIDENT,
                                          self::TEST_RATIO_EXTERN);

        // the old allocated balances
        $old_balances = $workshops->map(fn ($workshop) => $workshop->balance()->allocated_balance);

        // we give the payer's id as the receiver's id
        // because Checkout::studentsCouncil()->handler_id returns null
        $user->payKKTNetreg($user->id, self::TEST_KKT, self::TEST_NETREG);
        // since this uses the config values to generate balances,
        // we have to redo it:
        WorkshopBalance::generateBalances(Semester::current(),
                                          self::TEST_RATIO_RESIDENT,
                                          self::TEST_RATIO_EXTERN);

        $this->assertEquals($user->paidKKT(), self::TEST_KKT);

        $new_balances = $workshops->map(fn ($workshop) => $workshop->balance()->allocated_balance);
        $diffs = $new_balances->zip($old_balances)->map(fn ($pair) => $pair[0] - $pair[1]);

        $amount_for_workshops = intval(self::TEST_KKT
                                       * ($user->isExtern() ? self::TEST_RATIO_EXTERN
                                                            : self::TEST_RATIO_RESIDENT));
        $this->assertEquals($diffs->sum(), $amount_for_workshops);
    }

    /**
     * The concrete call of testPayment for externs with one workshop.
     */
    public function testExternPaymentWithOneWorkshop()
    {
        $user = User::factory()->create();
        $user->setStatus(SemesterStatus::ACTIVE);  // important!

        $user->setCollegist(Role::EXTERN);
        $user->workshops()->sync(Workshop::all()->random(1));

        $this->testPayment($user);
    }

    /**
     * The concrete call of testPayment for residents with one workshop.
     */
    public function testResidentPaymentWithOneWorkshop()
    {
        $user = User::factory()->create();
        $user->setStatus(SemesterStatus::ACTIVE);  // important!

        $user->setCollegist(Role::RESIDENT);
        $user->workshops()->sync(Workshop::all()->random(1));

        $this->testPayment($user);
    }

    /**
     * The concrete call of testPayment for externs with two workshops.
     */
    public function testExternPaymentWithTwoWorkshops()
    {
        $user = User::factory()->create();
        $user->setStatus(SemesterStatus::ACTIVE);  // important!

        $user->setCollegist(Role::EXTERN);
        $user->workshops()->sync(Workshop::all()->random(2));

        $this->testPayment($user);
    }

    /**
     * The concrete call of testPayment for residents with two workshops.
     */
    public function testResidentPaymentWithTwoWorkshops()
    {
        $user = User::factory()->create();
        $user->setStatus(SemesterStatus::ACTIVE);  // important!

        $user->setCollegist(Role::RESIDENT);
        $user->workshops()->sync(Workshop::all()->random(2));

        $this->testPayment($user);
    }
}
