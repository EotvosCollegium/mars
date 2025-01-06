@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Admin</a>
<a href="#!" class="breadcrumb">@lang('print.print')</a>
@endsection
@section('admin_module') active @endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                @include("dormitory.print.manage.modify")
            </div>
        </div>
    </div>
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                @include("dormitory.print.manage.free")
            </div>
        </div>
    </div>
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                @include("dormitory.print.manage.account_history")
            </div>
        </div>
    </div>
    <div class="col s12">
        @include("dormitory.print.free", ['route' => route('print.free-pages.index.admin'), 'admin' => true])
    </div>
    <div class="col s12">
        @include("dormitory.print.history", ['route' => route('print.print-job.index.admin'), 'admin' => true])
    </div>
</div>

@endsection