@extends('auth.application.app')

@section('form')

    @include('utils.user.profile-picture', ['user' => $user])

    <div class="card">
        <div class="card-content">
            @include('user.personal-information', ['user' => $user, 'application' => true])
        </div>
    </div>

@endsection
