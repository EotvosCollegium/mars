<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Network\InternetController;
use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\RoleObject;
use App\Models\Semester;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkshopBalance;
use App\Utils\CheckoutHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
                'users_not_paid' => User::hasToPayKKTNetreg()->get()
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
                ->get()
        ]);
    }

    /**
     * Pay kkt and netreg to the receiver given.
     * Also updates workshop balances
     * and the internet access expiry date.
     * Returns an array with the two transaction objects
     * and the new expiry date.
     *
     * Used here and in the tests.
     */
    public static function payKKTNetregLogic(User $payer, User $receiver, int $kkt_amount, int $netreg_amount): array
    {
        // Creating transactions
        $kkt = Transaction::create([
            'checkout_id' => Checkout::studentsCouncil()->id,
            'receiver_id' => $receiver->id,
            'payer_id' => $payer->id,
            'semester_id' => Semester::current()->id,
            'amount' => $kkt_amount,
            'payment_type_id' => PaymentType::kkt()->id,
            'comment' => null,
            'moved_to_checkout' => null,
        ]);

        $netreg = Transaction::create([
            'checkout_id' => Checkout::admin()->id,
            'receiver_id' => $receiver->id,
            'payer_id' => $payer->id,
            'semester_id' => Semester::current()->id,
            'amount' => $netreg_amount,
            'payment_type_id' => PaymentType::netreg()->id,
            'comment' => null,
            'moved_to_checkout' => null,
        ]);

        WorkshopBalance::generateBalances(Semester::current());

        $new_expiry_date = $payer->internetAccess->extendInternetAccess();

        return [$kkt, $netreg, $new_expiry_date];
    }

    /**
     * Pay kkt / netreg.
     */
    public function payKKTNetreg(Request $request)
    {
        $this->authorize('addKKTNetreg', Checkout::class);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'kkt' => 'required|integer|min:0',
            'netreg' => 'required|integer|min:0',
        ]);
        $validator->validate();

        $payer = User::findOrFail($request->user_id);
        // the current user will be the receiver
        [$kkt, $netreg, $new_internet_expire_date]
            = self::payKKTNetregLogic($payer, Auth::user(), $request->kkt, $request->netreg);

        $internet_expiration_message = null;
        if ($new_internet_expire_date !== null) {
            $internet_expiration_message = __('internet.expiration_extended', [
                'new_date' => Carbon::parse($new_internet_expire_date)->format('Y-m-d'),
            ]);
        }

        Mail::to($payer)->queue(new \App\Mail\Transactions(
            $payer->name,
            [$kkt, $netreg],
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
            'moved_to_checkout' => now()
        ]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
