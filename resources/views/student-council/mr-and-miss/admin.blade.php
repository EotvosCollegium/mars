@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">Választmány</a>
    <a href="{{ route('mr_and_miss.index') }}" class="breadcrumb"
       style="cursor: pointer">Mr. és Miss Eötvös</a>
    <a href="#!" class="breadcrumb">Szerkesztés</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    @foreach ($errors as $error)
        <blockquote class="error">{{ $error }}</blockquote>
    @endforeach
    @include('student-council.mr-and-miss.period')
    @include('student-council.mr-and-miss.categories')
@endsection
