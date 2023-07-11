@can('viewAny', \App\Models\WifiConnection::class)
<span class="card-title">Wifi csatlakozások: felhasználók a limit felett</span>
<table>
    <thead>
        <tr>
            <th>
                Felhasználó
            </th>
            <th>
                Engedélyezett limit
            </th>
            <th>
                Csatlakozások száma
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users->where('reachedWifiConnectionLimit', true) as $user)
        <tr>
            <td>
                <b>{{ $user->name }}</b>
            </td>
            <td>
                {{ $user->internetAccess->wifi_connection_limit }}
            </td>
            @include('network.wifi_connections.show')
            <td>
                <div class="right">
                    @can('view', $user)
                    <a href="{{ route('admin.internet.wifi_connections.approve', ['user' => $user->id]) }}" class="btn-floating waves-effect">
                        <i class="material-icons">exposure_plus_1</i></a>
                    @endcan
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td>
                @lang('general.nothing_to_show')
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<small>*Zöld: > 10 nap, Sárga: 5 és 10 nap között, Piros: < 5 nap</small>
@endcan
