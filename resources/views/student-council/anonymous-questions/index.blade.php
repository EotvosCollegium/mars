@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('anonymous_questions.anonymous_questions')</a>
@endsection
@section('secretariat_module') active @endsection

@php
// let the current semester be found based on the periodic event itself
// beware: it might be null
$periodicEvent = app(\App\Http\Controllers\Secretariat\SemesterEvaluationController::class)->periodicEvent();
$currentSemester = $periodicEvent?->semester;
@endphp

@section('content')


<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <p>@lang('anonymous_questions.creation_for_current_only')</p>
            </div>
        </div>
    </div>
</div>

@foreach(App\Models\Semester::allUntilCurrent()
    ->sortBy(function (App\Models\Semester $semester) {
        return $semester->getStartDate();
    })->reverse()
    as $semester)

@php
$isActive = $currentSemester?->id == $semester->id
            && (!$periodicEvent->endDate()?->isPast() ?? false);
@endphp

<ul class="collapsible">
    <li @if($isActive) class="active" @endif>
        <div class="collapsible-header">
                <b>{{$semester->tag}}</b>
        </div>
        <div class="collapsible-body">
            <div class="row" style="margin: 0">
                <b style="font-size: 120%;">
                    @lang('anonymous_questions.number_of_fillings'):
                    {{$semester->answerSheets->count()}}
                </b>
                <x-input.button :href="route('anonymous_questions.export_answers', $semester)"
                    class="right" :text="__('anonymous_questions.export')" />
            </div>

            <ul class="collection">
                @foreach($semester->questions as $question)
                <li class="collection-item">
                    <b style="font-size: 110%;">{{$question->title}}</b>
                    @if($question->has_long_answers)
                    (@lang('anonymous_questions.has_long_answers'))
                    @else
                    @if($question->isMultipleChoice()) (@lang('anonymous_questions.is_multiple_choice')) @endif
                    <ul class="collection">
                        @foreach($question->options as $option)
                        <li class="collection-item">
                            {{$option->title}}:
                            {{$option->votes}}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @endforeach
            </ul>

            @if($isActive)
            <div class="row" style="margin: 0">
                <x-input.button :href="route('anonymous_questions.create', $semester)"
                        class="right green" :text="__('anonymous_questions.create_question')" />
            </div>
            @endif
        </div>
    </li>
</ul>
@endforeach

@endsection
