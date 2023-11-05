{{-- desktop profile pic --}}
<div class="card horizontal hide-on-small-only">
    <div class="card-image">
        <img src="{{ url($user->profilePicture ? $user->profilePicture->path : '/img/avatar.png') }}"
                style="max-width:300px">
    </div>
    <div class="card-stacked">
        <div class="card-content">
            @if ($user->profilePicture)
            <form method="POST" action="{{ route('users.delete.profile-picture', ['user' => $user]) }}">
                @csrf
                @method('delete')
                <div class="right">
                    <x-input.button only_input text="user.delete_picture"/>
                </div>
            </form>
            @endif
            <div class="card-title">
                @if(\Route::current()->getName() == 'profile') {{$user->name}} @else Profilkép @endif
            </div>
        </div>
        <form action="{{ route('users.update.profile-picture', ['user' => $user]) }}" method="POST"
                enctype="multipart/form-data">
            @csrf
            <div class="card-action valign-center">
                <x-input.file s="12" xl="8" id="picture" style="margin-top:auto" accept=".jpg,.png,.jpeg"
                                text="general.browse" required/>
                <x-input.button only_input class="right" style="margin-top: 20px" text="general.upload"/>
            </div>
        </form>
    </div>
</div>


{{-- mobile profile pic --}}
<div class="card horizontal hide-on-med-and-up">
    <div class="card-image">
        <img src="{{ url($user->profilePicture ? $user->profilePicture->path : '/img/avatar.png') }}">
    </div>
    <div class="card-stacked">
        <div class="card-content">
            <div class="card-title">@if(\Route::current()->getName() == 'profile') {{$user->name}} @else Profilkép @endif</div>
            <div class="row">
                <form action="{{ route('users.update.profile-picture', ['user' => $user]) }}" method="POST"
                        enctype="multipart/form-data">
                    @csrf
                    <x-input.file s="12" xl="8" id="picture" style="margin-top:auto" accept=".jpg,.png,.jpeg"
                                    text="general.browse" required/>
                    <x-input.button only_input class="right" style="margin-top: 20px" text="general.upload"/>
                </form>
                @if ($user->profilePicture)
                <form method="POST" action="{{ route('users.delete.profile-picture', ['user' => $user]) }}">
                    @csrf
                    <div class="right">
                        <input type="hidden" name="_method" value="delete">  {{-- this way, it will send a DELETE request --}}
                        <x-input.button only_input style="margin-top: 20px; margin-right: 0;" text="user.delete_picture"/>
                    </div>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>
