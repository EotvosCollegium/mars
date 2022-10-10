<?php

namespace App\Utils;

use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

trait CheckoutHandler
{
    /**
     * Returns the route name base, so some routes could be generated automatically.
     * For example the route base of economic_committee.transaction.delete is economic_committee.
     */
    abstract public static function routeBase();

    /**
     * Returns the Checkout model.
     */
    abstract public static function checkout(): Checkout;

    /**
     * Returns the data for the checkout view.
     * @param Checkout $checkout
     * @return array
     */
    private function getData($checkout)
    {
        /*@var User $user*/
        $user = Auth::user();

        if ($user->can('administrate', $checkout)) {
            $depts = User::withWhereHas('transactions_received', function ($query) use ($checkout) {
                $query
                    ->where('checkout_id', $checkout->id)
                    ->whereNull('moved_to_checkout');
            })->get();
            $current_balance_in_checkout = $checkout->balanceInCheckout();
        }

        $my_received_transactions = $user->transactions_received()
            ->where('checkout_id', $checkout->id)
            ->whereNull('moved_to_checkout')
            ->get();

        return [
            'current_balance' => $checkout->balance(),
            'current_balance_in_checkout' => $current_balance_in_checkout ?? null,
            'depts' => $depts ?? [],
            'my_received_transactions' => $my_received_transactions,
            'semesters' => $checkout->transactionsBySemesters(),
            'checkout' => $checkout,
            'route_base' => $this->routeBase()
        ];
    }

    /**
     * Gets the transactions of the certain payment types in the checkout.
     *
     * @param Checkout
     * @param  array  $payment_types  payment type ids
     */
    private function getTransactionsGroupedBySemesters(Checkout $checkout, array $payment_types)
    {
        $this->authorize('view', $checkout);

        return Semester::orderBy('year', 'desc')
            ->orderBy('part', 'desc')
            ->get()
            ->where('tag', '<=', Semester::current()->tag)
            ->load([
                'transactions' => function ($query) use ($checkout, $payment_types) {
                    $query->whereIn('payment_type_id', $payment_types);
                    $query->where('checkout_id', $checkout->id);
                    $query->with('type');
                },
                'workshopBalances.workshop',
            ]);
    }

    /**
     * Gets the users with the transactions received which are not added to checkout yet.
     *
     * @param  array  $payment_typed  payment type names
     * @param collection of the users with transactionsReceived attribute
     */
    public function getCollectedTransactions(array $payment_types)
    {
        $payment_type_ids = $this->paymentTypeIDs($payment_types);

        return User::collegists()->load(['transactionsReceived' => function ($query) use ($payment_type_ids) {
            $query->whereIn('payment_type_id', $payment_type_ids);
            $query->where('moved_to_checkout', null);
        }])->filter(function ($user, $key) {
            return $user->transactionsReceived->count();
        })->unique();
    }

    /**
     * Move all the transactions received by the given user to the checkout.
     * Moving the Netreg amount from the students council to the admins is not tracked.
     *
     * @param User
     * @param Checkout
     * @param User $user $transaction a transaction
     * @param Checkout $checkout the checkout
     * @return void
     */
    public function toCheckout(Request $request, User $user)
    {
        $this->authorize('administrate', $this->checkout());

        Transaction::where('receiver_id', $user->id)
            ->where('checkout_id', $this->checkout()->id)
            ->where('moved_to_checkout', null)
            ->update(['moved_to_checkout' => Carbon::now()]);

        // TODO send a receipt email to the receiver

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Create a basic expense transaction in the checkout.
     * Income can only be added as KKT/Netreg currently.
     * The receiver and the payer also will be the authenticated user
     * (since this does not mean a payment between users, just a transaction from the checkout).
     * The only exception for the payer is when the checkout handler administrates a transaction payed by someone else.
     *
     * @param Request
     * @param Checkout
     * @return void
     */
    public function addExpense(Request $request)
    {
        $this->authorize('createTransaction', $this->checkout());

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'amount' => 'required|integer|min:0',
            'payer' => 'exists:users,id'
        ]);
        $validator->validate();

        $user = $request->user();
        $isCheckoutHandler = $user->can('administrate', $this->checkout());

        $payer = $request->has('payer') && $isCheckoutHandler ? User::find($request->input('payer')) : $user;
        $moved_to_checkout = $request->has('in_checkout') && $isCheckoutHandler;

        Transaction::create([
            'checkout_id'       => $this->checkout()->id,
            'receiver_id'       => Auth::user()->id,
            'payer_id'          => $payer->id,
            'semester_id'       => Semester::current()->id,
            'amount'            => (-1) * $request->amount,
            'payment_type_id'   => PaymentType::expense()->id,
            'comment'           => $request->comment,
            'moved_to_checkout' => $moved_to_checkout ? Carbon::now() : null,
        ]);

        // TODO send an email to the checkout handler and the payer

        return back()->with('message', __('general.successfully_added'));
    }

    /**
     * Delete a transaction.
     *
     * @param Transaction $transaction to be deleted
     * @return void
     */
    public function deleteTransaction(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();

        //TODO send email to receiver and payer

        return redirect()->back()->with('message', __('general.successfully_deleted'));
    }
}
