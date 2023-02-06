@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent }}!</h1>
<p>
@if($additional_message)
    {{ $additional_message }}
@else
    @lang('checkout.transactions')
@endif

</p>
<ul>
@foreach($transactions as $transaction)
<li>
{{ $transaction->comment }}: {{ abs($transaction->amount) }} Ft.
@if($transaction->payer!=$transaction->receiver)
    (@lang('checkout.receiver'): {{ $transaction->receiver?->name ?? "N/A" }},
    @lang('checkout.payer'): {{ $transaction->payer?->name ?? "N/A" }})
@elseif($recipent != $transaction->payer?->name)
    (@lang('checkout.payer'): {{ $transaction->payer?->name ?? "N/A" }})
@endif
</li>
@endforeach
</ul>

<p>@lang('mail.administrators')</p>
@endcomponent
