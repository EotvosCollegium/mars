{{-- desktop profile pic --}}
<div class="card horizontal hide-on-med-and-down">
    <div class="card-image">
        <img src="{{ url($user->profilePicture ? $user->profilePicture->path : '/img/avatar.png') }}"
                style="max-width:300px; max-height:500px;">
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
                <x-input.file s="12" l="7" xl="8" id="picture" style="margin-top:auto" accept=".jpg,.png,.jpeg"
                                text="general.browse" required helper=".jpg,.png,.jpeg fájlok tölthetőek fel,
                                maximum {{config('custom.general_file_size_limit')/1000}} MB-os méretig."/>
                <x-input.button only_input class="right" style="margin-top: 20px" text="general.upload"/>
            </div>
        </form>

    </div>
</div>


{{-- mobile profile pic --}}
<div class="card hide-on-large-only">
    <div class="card-image" >
        <img style="max-height: 500px; object-fit: contain;" src="{{ url($user->profilePicture ? $user->profilePicture->path : '/img/avatar.png') }}">
    </div>
        <div class="card-content">
            <div class="card-title">@if(\Route::current()->getName() == 'profile') {{$user->name}} @else Profilkép @endif</div>
            <div class="row">
                <form action="{{ route('users.update.profile-picture', ['user' => $user]) }}" method="POST"
                        enctype="multipart/form-data">
                    @csrf
                    <x-input.file s="12" id="picture" style="margin-top:auto; margin-bottom: 20px" accept=".jpg,.png,.jpeg"
                                    text="general.browse" required
                                    helper=".jpg,.png,.jpeg fájlok tölthetőek fel, maximum {{config('custom.general_file_size_limit')/1000}} MB-os méretig."/>
                    <x-input.button only_input class="right" style="margin-top: 10px; margin-left: 10px" text="general.upload"/>
                </form>
                @if ($user->profilePicture)
                <form method="POST" action="{{ route('users.delete.profile-picture', ['user' => $user]) }}">
                    @csrf
                    @method('delete')
                    <div class="right">
                        <x-input.button only_input style="margin-top: 10px" text="user.delete_picture"/>
                    </div>
                </form>
                @endif
            </div>

    </div>
</div>
