{{-- Input: $transaction, $paymentType  --}}
@foreach($transactions->where('payment_type_id', $paymentType->id) as $transaction)
<tr>
    <td>{{ $transaction->comment }}</td>
    @can('administrate', $checkout)
        <td class="center-align">{{ $transaction->payer?->name }}</td>
        <td class="center-align"><nobr>{{ $transaction->paid_at?->format('Y. m. d.') ?? "-" }}</nobr></td>
        <td class="center-align"><nobr>{{ $transaction->moved_to_checkout?->format('Y. m. d.') ?? "-" }}</nobr></td>
    @else
    <td></td><td></td><td></td>
    @endif
    <td class="center-align"><nobr>{{ $transaction->created_at->format('Y. m. d.') }}</nobr></td>
    <td class="right-align">
        {{ number_format($transaction->amount, 0, '.', ' ') }} Ft
    </td>
    <!-- delete transaction -->
    @can('delete', $transaction)
        <td>
            <x-input.button :href="route('admin.checkout.transaction.delete', ['transaction' => $transaction])" icon="delete" floating class="btn-small red right" />
        </td>
    @endcan
</tr>
@endforeach
