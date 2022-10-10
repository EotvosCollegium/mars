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
    abstract public static function checkout() : Checkout;

    private function getData($checkout)
    {
        /*@var User $user*/
        $user = Auth::user();

        if($user->can('administrate', $checkout)){
            $depts = User::withWhereHas('transactions_received', function($query) use ($checkout) {
                $query
                    ->where('checkout_id', $checkout->id)
                    ->whereNull('moved_to_checkout')
                    ->whereIn('payment_type_id', [PaymentType::income()->id, PaymentType::expense()->id]);
            })->get();
            $current_balance_in_checkout = $checkout->balanceInCheckout();
        }

        $my_received_transactions = $user->transactions_received()
            ->where('checkout_id', $checkout->id)
            ->whereNull('moved_to_checkout')
            ->whereIn('payment_type_id', [PaymentType::income()->id, PaymentType::expense()->id])
            ->get();

        return [
            'current_balance' => $checkout->balance(),
            'current_balance_in_checkout' => $current_balance_in_checkout ?? null,
            'depts' => $depts ?? null,
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

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Create a basic (income/expense) transaction in the checkout.
     *
     * @param Request
     * @param Checkout
     * @return void
     */
    public function addTransaction(Request $request)
    {
        $this->authorize('administrate', $this->checkout());

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'amount' => 'required|integer|min:0',
            'receiver' => 'required|exists:users,id',
            'payer' => 'required|exists:users,id',
            'type' => 'required|in:EXPENSE,INCOME',
        ]);
        $validator->validate();

        $type = PaymentType::getFromCache($request->type)->id;
        $payer = User::findOrFail($request->payer);
        $receiver = User::findOrFail($request->receiver);

        $transaction = Transaction::create([
            'checkout_id' => $this->checkout()->id,
            'receiver_id' => $receiver->id,
            'payer_id' => $payer->id,
            'semester_id' => Semester::current()->id,
            'amount' => $request->amount * ($request->type == 'EXPENSE' ? -1 : 1),
            'payment_type_id' => $type,
            'comment' => $request->comment,
            'moved_to_checkout' => ($request->in_checkout ? Carbon::now() : null),
        ]);

        Mail::to($payer)->queue(new \App\Mail\PayedTransaction($payer->name, [$transaction]));

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
