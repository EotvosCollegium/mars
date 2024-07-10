@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('anonymous_questions.anonymous_questions')</a>
@endsection
@section('secretariat_module') active @endsection

@php
// let us decide the openness of semesters
// based on the periodic event itself
$anonymousQuestionController = app(\App\Http\Controllers\StudentsCouncil\AnonymousQuestionController::class);
@endphp

@section('content')

@foreach(App\Models\Semester::orderBy('year', 'desc')->orderBy('part', 'desc')->get() as $semester)
<ul class="collapsible" @if(session()->get('section') == $semester->id) class="active" @endif>
    <li @if(session()->get('section') == $semester->id) class="active" @endif>
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

            @if($anonymousQuestionController->canAddQuestionTo($semester))
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
