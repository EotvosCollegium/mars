<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\ConfigurableValue;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkshopBalance;
use App\Utils\CheckoutHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EconomicController extends Controller
{
    use CheckoutHandler;

    /**
     * Return the route base for the checkout of the students council.
     */
    public static function routeBase(): string
    {
        return 'economic_committee';
    }

    /**
     * Return the checkout of the students council.
     */
    public static function checkout(): Checkout
    {
        return Checkout::studentsCouncil();

    }

    /**
     * Show the checkout page.
     */
    public function index()
    {
        $this->authorize('view', $this->checkout());

        return view(
            'student-council.economic-committee.app',
            array_merge($this->getData($this->checkout()), [
                'users_not_paid' => User::hasToPayKKTNetreg()->with('educationalInformation')->get(),
                'kkt_non_payers_with_rooms' => User::hasToPayKKTNetreg()->has('room')->pluck('id')->toArray(),
                'total_kkt_resident' => ConfigurableValue::getNumber('TOTAL_KKT_RESIDENT'),
                'total_kkt_extern' => ConfigurableValue::getNumber('TOTAL_KKT_EXTERN'),
                'workshop_balance_resident' => ConfigurableValue::getNumber('WORKSHOP_BALANCE_RESIDENT'),
                'workshop_balance_extern' => ConfigurableValue::getNumber('WORKSHOP_BALANCE_EXTERN'),
            ])
        );
    }

    /**
     * Show the kkt / netreg page.
     */
    public function indexKKTNetreg()
    {
        $this->authorize('addKKTNetreg', Checkout::class);

        return view('student-council.economic-committee.kktnetreg', [
            'users_not_paid' => User::hasToPayKKTNetreg()->get(),
            'transactions' => Transaction::whereIn('payment_type_id', [PaymentType::kkt()->id, PaymentType::netreg()->id])
                ->where('semester_id', Semester::current()->id)
                ->get(),
        ]);
    }

    public function configureKKTNetreg(Request $request)
    {
        $this->authorize('administrate', Checkout::studentsCouncil());

        Validator::make($request->all(), [
            'total_kkt_resident' => 'required|integer|gte:workshop_balance_resident|min:0',
            'total_kkt_extern' => 'required|integer|gte:workshop_balance_extern|min:0',
            'workshop_balance_resident' => 'required|integer|min:0',
            'workshop_balance_extern' => 'required|integer|min:0',
        ])->validate();

        ConfigurableValue::getConfigurableValue('TOTAL_KKT_RESIDENT')->update(['raw_value' => $request->total_kkt_resident]);
        ConfigurableValue::getConfigurableValue('TOTAL_KKT_EXTERN')->update(['raw_value' => $request->total_kkt_extern]);
        ConfigurableValue::getConfigurableValue('WORKSHOP_BALANCE_RESIDENT')->update(['raw_value' => $request->workshop_balance_resident]);
        ConfigurableValue::getConfigurableValue('WORKSHOP_BALANCE_EXTERN')->update(['raw_value' => $request->workshop_balance_extern]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Pay kkt / netreg.
     */
    public function payKKTNetreg(Request $request)
    {
        $this->authorize('addKKTNetreg', Checkout::class);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'calculated_amount' => 'required|integer|min:0',
        ]);
        $validator->validate();

        $payer = User::findOrFail($request->user_id);

        $expected_amount = $payer->room ? ConfigurableValue::getNumber('TOTAL_KKT_RESIDENT') : ConfigurableValue::getNumber('TOTAL_KKT_EXTERN');
        if ($request->calculated_amount != $expected_amount) {
            Log::error('KKT/Netreg payment failed due to incorrect amount', [
                'user_id' => Auth::id(),
                'payer_id' => $payer->id,
                'expected_amount' => $expected_amount,
                'actual_amount' => $request->calculated_amount,
            ]);

            return redirect()->back()->with('error', 'Sikertelen befizetés: az összeg nem megfelelő. Keresd fel a Rendszergazdákat!');
        }

        $transaction = Transaction::create([
            'checkout_id' => Checkout::studentsCouncil()->id,
            'receiver_id' => Auth::id(),
            'payer_id' => $payer->id,
            'semester_id' => Semester::current()->id,
            'amount' => $expected_amount,
            'payment_type_id' => PaymentType::kkt()->id,
            'comment' => null,
            'moved_to_checkout' => null,
        ]);

        $new_internet_expire_date = $payer->internetAccess->extendInternetAccess();
        $internet_expiration_message = null;
        if ($new_internet_expire_date !== null) {
            $internet_expiration_message = __('internet.expiration_extended', [
                'new_date' => Carbon::parse($new_internet_expire_date)->format('Y-m-d'),
            ]);
        }

        Mail::to($payer)->queue(new \App\Mail\Transactions(
            $payer->name,
            [$transaction],
            "Tranzakció létrehozva",
            $internet_expiration_message
        ));

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Recalculate the workshop balances in the current semester.
     */
    public function calculateWorkshopBalance()
    {
        $this->authorize('calculateWorkshopBalance', Checkout::class);

        WorkshopBalance::generateBalances(Semester::current());

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Modify a workshop balance.
     */
    public function modifyWorkshopBalance(WorkshopBalance $workshop_balance, Request $request)
    {
        $this->authorize('administrate', Checkout::studentsCouncil());

        Validator::make($request->all(), [
            'amount' => 'required|integer',
        ])->validate();

        $workshop_balance->increment('used_balance', $request->amount);
        Transaction::create([
            'checkout_id' => Checkout::studentsCouncil()->id,
            'receiver_id' => user()->id,
            'semester_id' => $workshop_balance->semester->id,
            'amount' => (-1) * $request->amount,
            'payment_type_id' => PaymentType::workshopExpense()->id,
            'moved_to_checkout' => now(),
        ]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
