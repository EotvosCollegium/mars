<?php

namespace App\Utils;

use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Transaction;
use App\Mail\Transactions;
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
            $depts = User::withWhereHas('transactionsReceived', function ($query) use ($checkout) {
                $query
                    ->where('checkout_id', $checkout->id)
                    ->whereNull('paid_at');
            })->get();
            $transactions_not_in_checkout = $checkout->transactions()->whereNull('moved_to_checkout')->sum('amount');
            $current_balance_in_checkout = $checkout->balanceInCheckout();
        }

        $my_received_transactions = $user->transactionsReceived()
            ->where('checkout_id', $checkout->id)
            ->whereNull('paid_at')
            ->get();

        return [
            'current_balance' => $checkout->balance(),
            'current_balance_in_checkout' => $current_balance_in_checkout ?? null,
            'depts' => $depts ?? [],
            'my_received_transactions' => $my_received_transactions,
            'transactions_not_in_checkout' => $transactions_not_in_checkout ?? 0,
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
     * Mark all the transactions received by the current user as paid.
     */
    public function markAsPaid(Request $request, User $user)
    {
        $this->authorize('administrate', $this->checkout());

        $transactions = Transaction::where('receiver_id', $user->id)
            ->where('checkout_id', $this->checkout()->id)
            ->whereNull('paid_at')->get();

        Mail::to(Auth::user())->queue(new Transactions(Auth::user()->name, $transactions, __('checkout.transaction_updated'), "A fenti tranzakciókat kifizetted."));
        Mail::to($user)->queue(new Transactions($user->name, $transactions, __('checkout.transaction_updated'), "A fenti tranzakciók ki lettek fizetve számodra."));

        Transaction::where('receiver_id', $user->id)
            ->where('checkout_id', $this->checkout()->id)
            ->whereNull('paid_at')
            ->update(['paid_at' => Carbon::now()]);


        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Move all the transactions to the checkout.
     */
    public function toCheckout(Request $request)
    {
        $this->authorize('administrate', $this->checkout());

        $user = Auth::user();

        $transactions = Transaction::where('checkout_id', $this->checkout()->id)
            ->where('moved_to_checkout', null)->get();

        Mail::to($user)->queue(new Transactions($user->name, $transactions, __('checkout.transaction_updated'), "A tranzakciók új státusza: szinkronizálva a kasszával. (Ettől függetlenül még tartozhatsz embereknek, nézd meg Uránban!)"));

        Transaction::where('checkout_id', $this->checkout()->id)
            ->where('moved_to_checkout', null)->update(['moved_to_checkout' => Carbon::now()]);

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

        $user = $request->has('payer') && $isCheckoutHandler ? User::find($request->input('payer')) : $request->user();
        $paid = $request->has('paid') && $isCheckoutHandler;

        $transaction = Transaction::create([
            'checkout_id'       => $this->checkout()->id,
            'receiver_id'       => $user->id,
            'payer_id'          => $user->id,
            'semester_id'       => Semester::current()->id,
            'amount'            => (-1) * $request->amount,
            'payment_type_id'   => PaymentType::expense()->id,
            'comment'           => $request->comment,
            'paid_at'           => $paid ? Carbon::now() : null,
        ]);

        Mail::to($user)->queue(new Transactions($user->name, [$transaction], __('checkout.transaction_created')));

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

        if ($transaction->payer) {
            Mail::to($transaction->payer)->queue(new Transactions($transaction->payer->name, [$transaction], __('checkout.transaction_deleted'), __('checkout.transactions_has_been_deleted')));
        }
        if ($transaction->receiver) {
            Mail::to($transaction->receiver)->queue(new Transactions($transaction->receiver->name, [$transaction], __('checkout.transaction_deleted'), __('checkout.transactions_has_been_deleted')));
        }

        $transaction->delete();

        return redirect()->back()->with('message', __('general.successfully_deleted'));
    }
}
