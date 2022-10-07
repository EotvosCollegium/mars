@extends('layouts.app')
@section('title')
<a href="#!" class="breadcrumb">@lang('user.update_tenant_status')</a>
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                {{-- <span class="card-title">@lang('secretariat.status')</span> --}}
                <form action="{{ route('users.tenant-update.update') }}" method="post">
                    @csrf
                    <div class="row">
                        <blockquote>
                            @lang('user.set_tenant_until')
                        </blockquote>
                        <x-input.datepicker
                            id='tenant_until'
                            required
                            locale='user'
                            :value="$user->personalInformation?->tenant_until" />
                    </div>
                    <div class="row">
                        <x-input.button class="right red" text="general.save" />
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>
@endsection