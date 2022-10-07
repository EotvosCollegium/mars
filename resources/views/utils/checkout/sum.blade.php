{{-- Input: $transaction, $paymentType  --}}
@php
    $sum = $transactions->where('payment_type_id', $paymentType->id)->sum('amount');
@endphp
<tr>
    <td>@lang('checkout.' . $paymentType->name)</td>
    @can('administrate', $checkout)
    <td></td>
    <td></td>
    @endcan
    <td></td>
    <td class="right"><nobr>{{ number_format($sum, 0, '.', ' ') }} Ft</nobr></td>
</tr>