{{-- Input: $transaction, $paymentType  --}}
@foreach($transactions->where('payment_type_id', $paymentType->id) as $transaction)
<tr>
    <td>{{ $transaction->comment }}</td>
    <td>{{ $transaction->created_at->format('Y. m. d.') }}</td>
    <td class="right"><nobr>{{ number_format($transaction->amount, 0, '.', ' ') }} Ft</nobr></td>
    <td class="right">
        <!-- delete transaction -->
        @can('delete', $transaction)
            <x-input.button :href="route('admin.checkout.transaction.delete', ['transaction' => $transaction])" icon="delete" floating class="btn-small red" />
        @endcan
    </td>
</tr>
@endforeach