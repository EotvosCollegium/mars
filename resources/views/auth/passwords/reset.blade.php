@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="card-content">
                    <div class="card-title"> @lang('passwords.resetpwd')</div>
                    <div class="row">
                        <x-input.text id="email"      text="registration.email" type="email" :value="$email" required autocomplete="email" autofocus/>
                        <x-input.text id="password"   text="registration.password" type="password" required autocomplete="new-password"/>
                        <x-input.text id="confirmpwd" text="registration.confirmpwd" name="password_confirmation" type="password" required autocomplete="new-password"/>
                    </div>
                </div>
                <div class="card-action">
                    <div class="row">
                        <x-input.button only_input text="passwords.resetpwd" class="right"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
