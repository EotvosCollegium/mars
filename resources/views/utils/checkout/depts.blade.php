@can('administrate', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.depts') </span>
        <blockquote>@lang('checkout.pay_depts_descr')</blockquote>
        <table><tbody>
            @forelse($depts as $user)
              <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->transactionsReceived->sum('amount') }} Ft</td>
                    <td>
                        <form method="POST" action="{{ route($route_base . '.pay', ['user' => $user]) }}">
                            @csrf
                            <x-input.button floating class="right green" icon="payments"/>
                        </form>
                    </td>
                </tr>
            @empty
                Jelenleg nincs tartozás a rendszerben...
            @endforelse
        </tbody></table>
    </div>
</div>
@endcan

