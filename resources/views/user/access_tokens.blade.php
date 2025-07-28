<table>
    <tr>
        <th>@lang('general.id')</th>
        <th>@lang('general.name')</th>
        <th>@lang('user.access_token.created_at')</th>
        <th>@lang('user.access_token.valid_until')</th>
        <th class="right">@lang('user.access_token.revoke')</th>
    </tr>
    @foreach($user->tokens as $token)
        <tr>
            <td>{{ $token->id }}</td>
            <td>{{ $token->name }}</td>
            <td>{{ $token->created_at }}</td>
            <td>{{ $token->expires_at ? $token->expires_at : 'N/A' }}</td>
            <td>
                <form action="{{ route('users.access-token.revoke', ['user' => $user, 'token' => $token]) }}" method="post">
                    <input type="hidden" name="_method" value="DELETE">
                    @csrf
                    <x-input.button only_input floating class="red right" icon="delete" />
                </form>
            </td>
        </tr>
    @endforeach

    <div class="row">
        <div class="col s12">
            <form action="{{ route('users.access-token.create', ['user' => $user]) }}" method="post">
                @csrf
                <x-input.text s=6 id='access-token-name' name="name" text="general.name" required asterisk />
                <x-input.datepicker s=3 id='access-token-expires-at' name="expires_at" text="user.access_token.valid_until" :min="\Carbon\Carbon::now()->format('Y-m-d')" />
                <x-input.button s=3 class="right green" text="user.access_token.create" />
            </form>
        </div>
    </div>
</table>

@if (session('new-access-token'))
<div id="token-modal" class="modal">
    <div class="modal-content">
        <h4>@lang('user.access_token.created')</h4>
        <p>@lang('user.access_token.created_description')</p>
        <p><code>{{ session('new-access-token') }}</code></p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect btn">@lang('general.close')</a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var Modalelem = document.getElementById('token-modal');
        var instance = M.Modal.init(Modalelem);
        instance.open();
    });
</script>
@endpush

@endif
