@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent }}!</h1>
<p>
@lang('checkout.transactions'):
</p>
<ul>
@foreach($transactions as $transaction)
<li>
{{ $transaction->comment }}: {{ abs($transaction->amount) }} Ft.
@if($transaction->payer!=$transaction->receiver)
(@lang('checkout.receiver'): {{ $transaction->receiver?->name ?? "N/A" }},
@lang('checkout.payer'): {{ $transaction->payer?->name ?? "N/A" }})
@endif
</li>
@endforeach
</ul>
@if($additional_message)
<p>
{{ $additional_message }}
</p>
@endif
<p>@lang('mail.administrators')</p>
@endcomponent