@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">Választmány</a>
    <a href="{{ route('mr_and_miss.index') }}" class="breadcrumb"
        style="cursor: pointer">Mr. és Miss Eötvös</a>
    <a href="#!" class="breadcrumb">Eredmények</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    @forelse($results->groupBy('mr') as $mr => $results2)
        @foreach ($results2->groupBy('title') as $category => $nominees)
            <h5>@if($nominees->first()->custom)<i>@endif{{ $category }}@if($nominees->first()->custom)</i>@endif</h5>
            @foreach ($nominees->sortBy('count') as $nominee)
                <p> {{ $nominee->count }}: {{ $nominee->name ?? $nominee->votee_name }}</p>
            @endforeach
        @endforeach
    @empty
        <blockquote>Még nincs beérkezett szavazat.</blockquote>
    @endforelse
@endsection
