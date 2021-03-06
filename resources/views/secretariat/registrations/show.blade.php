@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="{{ route('secretariat.registrations') }}" class="breadcrumb" style="cursor: pointer">@lang('admin.registrations')</a>
<a href="#!" class="breadcrumb">{{ $user->name }}</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')
<div class="row">
    <div class="col s12">

        <div class="card">
            <div class="card-content">
                <h5><b>{{ $user->name }}</b> ({{ $users_left}} @lang('document.left'))
                <div class="right">
                    <x-input.button :href="route('secretariat.registrations.reject', ['id' => $user->id, 'next' => true])" class="red" floating icon="block"/>
                    <x-input.button :href="route('secretariat.registrations.accept', ['id' => $user->id, 'next' => true])" class="green" floating icon="done"/>
                </div></h5>
            </div>
        </div>
        {{-- Personal information --}}
        @include('user.personal-information', ['user' => $user])
        {{-- Educational information --}}
        @include('user.educational-information', ['user' => $user])
    </div>
</div>
@endsection
