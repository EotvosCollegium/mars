<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Mail\ChangedPrintBalance;
use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Semester;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        if (user()->cannot('transferBalance', $printAccount)) {
            abort(403);
        }

        $otherAccount = $request->other_user ? User::find($request->get('other_user'))->printAccount : null;

        // This is a transfer between accounts
        if ($otherAccount !== null) {
            // Cannot transfer to yourself
            if ($otherAccount->user_id === $printAccount->user_id) {
                abort(400);
            }

            // Cannot transfer from other user's account (even if you are admin)
            if ($printAccount->user_id !== user()->id) {
                abort(403);
            }

            $amount = $request->get('amount');

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

            Mail::to($printAccount->user)->queue(new ChangedPrintBalance($printAccount->user, $request->get('amount'), user()->name));

            return redirect()->back()->with('message', __('general.successful_transaction'));
        }
        // This is a modification of the current account
        else {
            // Only admins can modify accounts
            if (user()->cannot('modify', $printAccount)) {
                abort(403);
            }

            $amount = $request->get('amount');

            if ($amount < 0 && $printAccount->balance < $amount) {
                $this->returnNoBalance();
            }

            $printAccount->update([
                'balance' => $printAccount->balance + $amount,
                'last_modified_by' => user()->id,
            ]);

            Mail::to($printAccount->user)->queue(new ChangedPrintBalance($printAccount->user, $request->get('amount'), user()->name));

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

            return redirect()->back()->with('message', __('general.successful_modification'));
        }
    }

    private function returnNoBalance()
    {
        return back()->withInput()->with('error', __('print.no_balance'));
    }
}
