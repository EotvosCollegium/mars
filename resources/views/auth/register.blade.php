@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col s12 l8 xl6 offset-l2 offset-xl3">
        <div class="card">
            <div class="card-image">
                <img src="/img/EC_building.jpg">
                <span class="card-title">
                    @if($user_type == \App\Models\Role::TENANT)
                    @lang('general.register_guest')
                    @else
                    @lang('general.register_collegist')
                    @endif
                </span>
            </div>
            <div class="card-content">
                @if($user_type == \App\Models\Role::COLLEGIST)
                <blockquote><a href="{{route('register.guest')}}" style="text-decoration: underline">@lang('registration.information_tenant')</a></blockquote>
                @endif
                @if($user_type == \App\Models\Role::TENANT || $application_open ?? false)
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    @foreach ($errors->all() as $error)
                    <blockquote class="error">{{ $error }}</blockquote>
                    @endforeach
                    <div class="row">
                        <input type="text" name="user_type" id="user_type" value="{{ $user_type }}" readonly hidden>
                        <x-input.text id='name' autofocus required autocomplete='name' text='user.name' />
                        <x-input.text id="email" type="email" text="user.email" required autocomplete="email" />
                        <x-input.text id="password" text="registration.password" type="password" required autocomplete="new-password" />
                        <x-input.text id="confirmpwd" text="registration.confirmpwd" name="password_confirmation" type="password" required autocomplete="new-password" />
                        @if ($user_type == \App\Models\Role::TENANT)
                        <x-input.text id='phone_number' type='tel' required pattern="[+][0-9]{1,4}[-\s()0-9]*" minlength="8" maxlength="18" text='user.phone_number' helper='+36 (20) 123-4567' />
                        <x-input.datepicker id='tenant_until' required text='user.tenant_until' />
                        @else
                        <div class="col">
                            <blockquote class="col">@lang('registration.information')</blockquote>
                        </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col s12 l8">
                            <p><label>
                                    <input type="checkbox" name="gdpr" id="qdpr" value="qdpr" required class="filled-in checkbox-color" />
                                    <span>@lang('auth.i_agree_to') <a href="/adatvedelmi_tajekoztato.pdf" target="_blank">@lang('auth.privacy_policy').</a></span>
                                </label></p>
                        </div>
                        <x-input.button l=4 class='right' text='general.register' />
                    </div>
                </form>
            @else
            <blockquote>Jelenleg nem lehet jelentkezni a Collegiumba.</blockquote>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection
