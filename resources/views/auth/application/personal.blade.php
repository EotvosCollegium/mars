@extends('auth.application.app')

@section('personal-active')
    active
@endsection

@section('form')

    <div class="card">
        <div class="card-content">
            @include('user.personal-information', ['user' => $user])
        </div>
    </div>

@endsection
