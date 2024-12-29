@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient->name }}!</h1>
<p>
@if ($print_account_holder->id === $recipient->id)
    @lang('print.your_balance_changed_descr', ['amount' => $amount, 'balance' => $print_account_holder->printAccount->balance, 'modifier' => $modifier])
@else
    @lang('print.others_balance_changed_descr', ['amount' => $amount, 'holder-name' => $print_account_holder->name, 'modifier' => $modifier])
@endif
</p>
@endcomponent
