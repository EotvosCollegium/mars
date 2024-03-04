@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="{{ route('routers') }}" class="breadcrumb" style="cursor: pointer">@lang('router.router_monitor')</a>
<a href="#!" class="breadcrumb">@lang('router.new')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('routers.store') }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">@lang('router.new')</span>
                    <div class="row">
                        <x-input.text s="6" type="text" text="router.ip" id="ip" maxlength="15" required/>
                        <x-input.text s="6" type="text" text="router.room" id="room" maxlength="5" required/>
                    </div>
                    <div class="row">
                        <x-input.text s="4" type="text" text="router.port" id="port"/>
                        <x-input.text s="4" type="text" text="router.type" id="type"/>
                        <x-input.text s="4" type="text" text="router.serial_number" id="serial_number"/>
                    </div>
                    <div><p>@lang('internet.mac_address')</p></div>
                    <div class="row">
                        <x-input.text s="4" type="text" text="WAN" id="mac_WAN"/>
                        <x-input.text s="4" type="text" text="2G/LAN" id="mac_2G_LAN"/>
                        <x-input.text s="4" type="text" text="5G" id="mac_5G"/>
                    </div>
                    <div class="row">
                        <x-input.text type="text" id="comment" text="general.comment" maxlength="255"/>
                    </div>
                    <div class="row">
                        <x-input.text s="6" type="date" id="date_of_acquisition" text="router.date_of_acquisition"/>
                        <x-input.text s="6" type="date" id="date_of_deployment" text="router.date_of_deployment"/>
                    </div>
                </div>
                <div class="card-action">
                    <div class="row" style="margin:0">
                        <x-input.button text="general.save" class="right"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

