@if(count($my_received_transactions))
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.my_received_transactions')</span>
        <div class="row">
            <div class="col s12">
            <table><tbody>
                @foreach($my_received_transactions as $transaction)
                <tr>
                    <td>
                        {{ $transaction->comment }}
                    </td>
                    <td>
                        @if($transaction->payer_id != Auth::user()->id)
                            {{ $transaction->payer->name }}
                        @endif
                    </td>
                    <td>{{ $transaction->amount }} Ft</td>
                    <td>
                        @can('delete', $transaction)
                        <a href="{{ route($route_base . '.transaction.delete', ['transaction' => $transaction->id]) }}"
                            class="btn-floating waves-effect right red">
                            <i class="material-icons">delete</i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody></table>
            </div>
        </div>
        <div class="row">
            <div class="col s8">
                <b>@lang('checkout.sum')</b>
            </div>
            <div class="col s4">
                <b>{{ $my_received_transactions->sum('amount') }} Ft</b>
            </div>
            <div class="col s12">
                <blockquote>@lang('checkout.depts_descr')</blockquote>
            </div>
        </div>
    </div>
</div>
@endif