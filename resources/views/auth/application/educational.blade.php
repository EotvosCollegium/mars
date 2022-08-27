@extends('auth.application.app')

@section('educational-active')
    active
@endsection

@section('form')

    <div class="card">
        <div class="card-content">
            @include('user.educational-information', ['user' => $user, 'application' => true])
        </div>
    </div>


@endsection
