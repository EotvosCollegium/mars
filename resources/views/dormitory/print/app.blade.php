@extends('layouts.app')
@section('title')
<i class="material-icons left">local_printshop</i>@lang('print.print')
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        @include("dormitory.print.print")
    </div>
    <div class="col s12">
        @include("dormitory.print.history", ['route' => route('print-job.index'), 'admin' => false])
    </div>
    <div class="col s12">
        @include("dormitory.print.free", ['route' => route('free-pages.index'), 'admin' => false])
    </div>
    <div class="col s12">
        @include("dormitory.print.send")
    </div>

</div>
@endsection
