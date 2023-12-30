@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('general.admin')</a>
    <a href="#!" class="breadcrumb">@lang('internet.internet')</a>
@endsection
@section('admin_module')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    @include('network.admin.internet_access')
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    @include('network.admin.mac_addresses')
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    @include('network.admin.wifi_connections')
                </div>
            </div>
        </div>
    </div>
@endsection
