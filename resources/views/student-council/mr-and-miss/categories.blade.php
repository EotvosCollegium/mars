@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('role.student-council')</a>
    <a href="{{route('mr_and_miss.vote')}}" class="breadcrumb" style="cursor: pointer">@lang('mr-and-miss.mr-and-miss')</a>
    <a href="#!" class="breadcrumb">@lang('mr-and-miss.mr-and-miss-categories')</a>

@endsection

@section('student_council_module')
    active
@endsection

@section('content')

{{-- Todo --}}

@endsection
