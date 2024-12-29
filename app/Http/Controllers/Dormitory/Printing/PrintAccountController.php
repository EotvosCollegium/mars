<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Mail\ChangedPrintBalance;
use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\PrintAccount;
use App\Models\Semester;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class PrintAccountController extends Controller
{
    /**
     * Updates balance of a `PrintAccount`.
     * This can be done in two ways: either by topping up the account by giving money to an admin
     * or by transfering money from one account to the other.
     */
    public function update(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer',
            'user' => 'required|exists:users,id', // Normally this would be a path parameter for the PrintAccount, but we can't do that because of the limitations of blade templates
            'other_user' => 'nullable|exists:users,id',
        ]);

        $printAccount = User::find($request->get('user'))->printAccount;

        // If user can not even transfer balance, we can stop here
        $this->authorize('transferBalance', $printAccount);

        $otherAccount = $request->other_user ? User::find($request->get('other_user'))->printAccount : null;

        // This is a transfer between accounts
        if ($otherAccount !== null) {
            return $this->transferBalance($printAccount, $otherAccount, $request->get('amount'));
        }
        // This is a modification of the current account
        else {
            return $this->modifyBalance($printAccount, $request->get('amount'));
        }
    }

    /**
     * Private helper function to transfer balance between two `PrintAccount`s.
     * @param PrintAccount $printAccount The account from which the money is transfered.
     * @param PrintAccount $otherAccount The account to which the money is transfered.
     * @param int $amount The amount of money to be transfered.
     * @return RedirectResponse
     */
    private function transferBalance(PrintAccount $printAccount, PrintAccount $otherAccount, int $amount)
    {
        DB::beginTransaction();
        // Cannot transfer to yourself
        if ($otherAccount->user_id === $printAccount->user_id) {
            abort(400);
        }

        // Cannot transfer from other user's account (even if you are admin)
        if ($printAccount->user_id !== user()->id) {
            abort(403);
        }

        // This would be effectively stealing printing money from the other account
        if ($amount < 0) {
            abort(400);
        }

        // Cannot transfer if there is not enough balance to be transfered
        if ($printAccount->balance < $amount) {
            return $this->returnNoBalance();
        }

        $printAccount->update([
            'balance' => $printAccount->balance - $amount,
            'last_modified_by' => user()->id,
        ]);

        $otherAccount->update([
            'balance' => $otherAccount->balance + $amount,
            'last_modified_by' => user()->id,
        ]);

        DB::commit();

        Mail::to($printAccount->user)->queue(new ChangedPrintBalance($printAccount->user, $printAccount->user, -$amount, user()->name));
        Mail::to($otherAccount->user)->queue(new ChangedPrintBalance($otherAccount->user, $printAccount->user, -$amount, user()->name));
        Mail::to($printAccount->user)->queue(new ChangedPrintBalance($printAccount->user, $otherAccount->user, $amount, user()->name));
        Mail::to($otherAccount->user)->queue(new ChangedPrintBalance($otherAccount->user, $otherAccount->user, $amount, user()->name));

        return redirect()->back()->with('message', __('general.successful_transaction'));
    }

    /**
     * Private helper function to modify the balance of a `PrintAccount`.
     * @param PrintAccount $printAccount The account to be modified.
     * @param int $amount The amount of money to be added or subtracted.
     * @return RedirectResponse
     */
    private function modifyBalance(PrintAccount $printAccount, int $amount)
    {
        DB::beginTransaction();
        // Only admins can modify accounts
        $this->authorize('modify', $printAccount);

        if ($amount < 0 && $printAccount->balance < abs($amount)) {
            return $this->returnNoBalance();
        }

        $printAccount->update([
            'balance' => $printAccount->balance + $amount,
            'last_modified_by' => user()->id,
        ]);

        $adminCheckout = Checkout::admin();
        Transaction::create([
            'checkout_id' => $adminCheckout->id,
            'receiver_id' => user()->id,
            'payer_id' => $printAccount->user->id,
            'semester_id' => Semester::current()->id,
            'amount' => $amount,
            'payment_type_id' => PaymentType::print()->id,
            'comment' => null,
            'moved_to_checkout' => null,
        ]);

        DB::commit();

        Mail::to(user())->queue(new ChangedPrintBalance(user(), $printAccount->user, $amount, user()->name));

        //Do not send duplicate emails
        if($printAccount->user->id !== user()->id) {
            Mail::to($printAccount->user)->queue(new ChangedPrintBalance($printAccount->user, $printAccount->user, $amount, user()->name));
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Private helper function to return a redirect with an error message if there is not enough balance.
     * @return RedirectResponse
     * @throws BindingResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function returnNoBalance()
    {
        return back()->withInput()->with('error', __('print.no_balance'));
    }
}
