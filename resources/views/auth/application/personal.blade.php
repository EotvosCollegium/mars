@extends('auth.application.app')

@section('form')

    <div class="card">
        <div class="card-content">
            @include('user.personal-information', ['user' => $user, 'application' => true])
        </div>
    </div>

@endsection
