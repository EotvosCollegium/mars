@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="#!" class="breadcrumb">@lang('admin.user_management')</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')

@livewire('list-users')

<div class="card">
    <div class="card-content">
        <div class="row">
            <div class="col s12 xl7">
                <span class="card-title">@lang('registration.invite')</span>
            </div>
            <form method="POST" action="{{ route('secretariat.registrations.invite') }}">
                @csrf
                <div class="col s12">
                    <blockquote>@lang('registration.invite_instructions')</blockquote>
                </div>
                <div class="col s12 m12 l4">
                    <x-input.text  id="name" locale="user" required />
                </div>
                <div class="col s12 m12 l4">
                    <x-input.text  id="email" type="email" locale="user" required />
                </div>
                <div class="col s12 m12 l4">
                    <x-input.button class="right" text="registration.invite_button" />
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
