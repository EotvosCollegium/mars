@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('role.student-council')</a>
    <a href="{{ route('mr_and_miss.vote') }}" class="breadcrumb"
        style="cursor: pointer">@lang('mr-and-miss.mr-and-miss')</a>
    <a href="#!" class="breadcrumb">@lang('mr-and-miss.mr-and-miss-results')</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    @foreach ($results->groupBy('mr') as $mr => $results2)
        @foreach ($results2->groupBy('title') as $category => $nominees)
            <h5>@if($nominees->first()->custom)<i>@endif{{ $category }}@if($nominees->first()->custom)</i>@endif</h5>
            @foreach ($nominees->sortBy('count') as $nominee)
                <p> {{ $nominee->count }}: {{ $nominee->name ?? $nominee->votee_name }}</p>
            @endforeach
        @endforeach
    @endforeach
@endsection
