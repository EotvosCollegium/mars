{{-- Input: $transaction, $paymentType  --}}
@php
    $sum = $transactions->where('payment_type_id', $paymentType->id)->sum('amount');
@endphp
<tr>
    <td>@lang('checkout.' . $paymentType->name)</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td class="right-align"><nobr>{{ number_format($sum, 0, '.', ' ') }} Ft</nobr></td>
    <td></td>
</tr>
