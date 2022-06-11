<!-- Admin site showing all applicants -->
@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">Felvételi jelentkezők</a>
@endsection

@section('content')
    <!-- TODO filter by workshop -->
    <h6>Összesen: <b class="right">{{$applications->count()}} jelentkező</b></h6>
    <hr>

@foreach($applications as $application)
    <!-- Todo hire/reject -->
    <a href="{{route('applications', ['id' => $application->user_id])}}">
    @include('auth.application.application', ['user' => $application->user, 'expanded' => false])
    </a>
@endforeach

@endsection
