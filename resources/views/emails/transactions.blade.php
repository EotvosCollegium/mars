@component('mail::message')
<h1>@lang('mail.dear') {{ $recipent }}!</h1>
<p>
@if($additional_message)
    {{ $additional_message }}
@else
    Tranzakciók:
@endif

</p>
<ul>
@foreach($transactions as $transaction)
<li>
{{ $transaction->comment }}: {{ abs($transaction->amount) }} Ft.
@if($transaction->payer!=$transaction->receiver)
    Átvevő: {{ $transaction->receiver?->name ?? "N/A" }},
    Fizető: {{ $transaction->payer?->name ?? "N/A" }})
@elseif($recipent != $transaction->payer?->name)
    Fizető: {{ $transaction->payer?->name ?? "N/A" }})
@endif
</li>
@endforeach
</ul>

<p>@lang('mail.administrators')</p>
@endcomponent
