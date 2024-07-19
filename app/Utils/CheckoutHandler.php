<?php

namespace App\Utils;

use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Transaction;
use App\Mail\Transactions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
    private function getData(Checkout $checkout): array
    {
        /** @var User $user */
        $user = user();

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
     * @param Checkout $checkout
     * @param array $payment_types payment type ids
     * @return Collection
     * @throws AuthorizationException
     */
    private function getTransactionsGroupedBySemesters(Checkout $checkout, array $payment_types): Collection
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
     * Mark all the transactions received by the current user as paid.
     * @param User $user
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function markAsPaid(User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('administrate', $this->checkout());

        $transactions = Transaction::where('receiver_id', $user->id)
            ->where('checkout_id', $this->checkout()->id)
            ->whereNull('paid_at')->get();

        Mail::to(user())->queue(new Transactions(user()->name, $transactions, "Tranzakció módosítva", "Az alábbi tranzakciókat kifizetted."));
        Mail::to($user)->queue(new Transactions($user->name, $transactions, "Tranzakció módosítva", "Az alábbi tranzakciókat kifizették neked."));

        Transaction::where('receiver_id', $user->id)
            ->where('checkout_id', $this->checkout()->id)
            ->whereNull('paid_at')
            ->update(['paid_at' => Carbon::now()]);


        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Move all the transactions to the checkout.
     * @throws AuthorizationException
     */
    public function toCheckout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('administrate', $this->checkout());

        $user = user();

        $transactions = Transaction::where('checkout_id', $this->checkout()->id)
            ->where('moved_to_checkout', null)->get();

        Mail::to($user)->queue(new Transactions($user->name, $transactions, "Tranzakció módosítva", "A tranzakciók új státusza: szinkronizálva a kasszával. (Ettől függetlenül még tartozhatsz embereknek, nézd meg Uránban!)"));

        Transaction::where('checkout_id', $this->checkout()->id)
            ->where('moved_to_checkout', null)->update(['moved_to_checkout' => Carbon::now()]);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Create a basic income transaction in the checkout.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addIncome(Request $request): RedirectResponse
    {
        $this->authorize('administrate', $this->checkout());
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'amount' => 'required|integer|min:0',
        ]);
        $validator->validate();

        Transaction::create([
            'checkout_id'       => $this->checkout()->id,
            'receiver_id'       => user()->id,
            'payer_id'          => null,
            'semester_id'       => Semester::current()->id,
            'amount'            => $request->amount,
            'payment_type_id'   => PaymentType::income()->id,
            'comment'           => $request->comment,
            'paid_at'           => Carbon::now(),
        ]);

        return back()->with('message', __('general.successfully_added'));

    }

    /**
     * Create a basic expense transaction in the checkout.
     * The receiver and the payer also will be the authenticated user
     * (since this does not mean a payment between users, just a transaction from the checkout).
     * The only exception for the payer is when the checkout handler administrates a transaction paid by someone else.
     * We require a receipt here.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addExpense(Request $request): RedirectResponse
    {
        $this->authorize('createTransaction', $this->checkout());

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'amount' => 'required|integer|min:0',
            'payer' => 'exists:users,id',
            'receipt' => 'required|mimes:pdf,jpg,jpeg,png,gif',
        ]);
        $validator->validate();

        $user = $request->has('payer') ? User::find($request->input('payer')) : user();
        $paid = $request->has('paid');

        // an output variable
        $transaction = null;

        // wrapping into one transaction so that if one of them fails,
        // the whole is reverted
        DB::transaction(function () use ($user, $request, $paid, &$transaction) {
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

            $path = $request->file('receipt')->store('receipts');
            $transaction->receipt()->create(['path' => $path, 'name' => 'receipt']);
        });

        Mail::to($user)->queue(
            new Transactions(
                $user->name,
                [$transaction],
                "Tranzakció létrehozva",
                "Az alábbi tranzakciók jöttek létre:"
            )
        );

        if ($this->checkout()->handler_id != $request->user()->id) {
            Mail::to($this->checkout()->handler)->queue(
                new Transactions(
                    $this->checkout()->handler->name,
                    [$transaction],
                    "Tranzakció létrehozva",
                    "Az alábbi tranzakciók jöttek létre:"
                )
            );
        }

        return back()->with('message', __('general.successfully_added'));
    }

    /**
     * Delete a transaction.
     *
     * @param Transaction $transaction to be deleted
     * @throws AuthorizationException
     */
    public function deleteTransaction(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction) {
            if ($transaction->receipt != null) {
                Storage::delete($transaction->receipt->path);
                $transaction->receipt()->delete();
            }

            $transaction->delete();
        });

        if ($transaction->payer) {
            Mail::to($transaction->payer)->queue(new Transactions($transaction->payer->name, [$transaction], "Tranzakció törölve", "A tranzakciók törlésre kerültek."));
        }
        if ($transaction->receiver && $transaction->receiver->id != $transaction->payer?->id) {
            Mail::to($transaction->receiver)->queue(new Transactions($transaction->receiver->name, [$transaction], "Tranzakció törölve", "A tranzakciók törlésre kerültek."));
        }

        return redirect()->back()->with('message', __('general.successfully_deleted'));
    }
}
