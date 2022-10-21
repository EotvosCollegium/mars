{{-- Input: $transaction, $paymentType  --}}
@foreach($transactions->where('payment_type_id', $paymentType->id) as $transaction)
<tr>
    <td>{{ $transaction->comment }}</td>
    @can('administrate', $checkout)
    <td>{{ $transaction->payer?->name }}</td>
    <td>{{ $transaction->receiver?->name }}</td>
    <td>{{ $transaction->paid_at ?? "-" }}</td>
    <td>{{ $transaction->moved_to_checkout ?? "-" }}</td>
    @else
    <td></td><td></td>
    @endif
    <td>{{ $transaction->created_at->format('Y. m. d.') }}</td>
    <td class="right">
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