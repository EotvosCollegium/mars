@can('administrate', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.depts') </span>
        <blockquote>@lang('checkout.depts_descr')</blockquote>
        <table><tbody>
            @foreach($collected_transactions as $collegist)
              <tr>
                    <td>{{ $collegist->name }}</td>
                    <td>{{ $collegist->transactionsReceived->sum('amount') }} Ft</td>
                    <td>
                        <form method="POST" action="{{ route($route_base . '.to_checkout') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{$collegist->id}}">
                            <x-input.button floating class="right green" icon="payments"/>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody></table>
    </div>
</div>
@endcan
