@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent }}!</h1>
<p>
@lang('checkout.transactions'):
</p>
<ul>
@foreach($transactions as $transaction)
<li>
{{ $transaction->comment }}: {{ $transaction->amount }} Ft. 
(@lang('checkout.receiver'): {{ $transaction->receiver?->name ?? "N/A" }},
@lang('checkout.payer'): {{ $transaction->payer?->name ?? "N/A" }})
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