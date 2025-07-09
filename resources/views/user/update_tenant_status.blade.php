@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">@lang('user.update_tenant_status')</a>
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">@lang('user.update_tenant_status')</span>
                    <blockquote>@lang('user.set_tenant_until')</blockquote>
                    <form method="POST" action="{{ route('users.update.tenant_until', ['user' => user()]) }}">
                        @csrf
                        <div class="row">
                            <x-input.datepicker
                                id='tenant_until'
                                required
                                text='user.tenant_until'
                                :value="user()->personalInformation?->tenant_until"/>
                            <x-input.button class="right" text="general.save"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
