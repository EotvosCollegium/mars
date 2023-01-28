@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="{{ route('routers') }}" class="breadcrumb" style="cursor: pointer">@lang('router.router_monitor')</a>
<a href="#!" class="breadcrumb">@lang('general.add_new')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('voting.add_sitting') }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">@lang('voting.new_sitting')</span>
                    <div class="row">
                        <x-input.text s="12" type="text" text="voting.sitting_title" id="title" maxlength="50" required/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ route('voting') }}" class="waves-effect btn">@lang('voting.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('voting.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

