@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('anonymous_questions.anonymous_questions')</a>
@endsection
@section('secretariat_module') active @endsection


@section('content')

@foreach(App\Models\Semester::orderBy('year', 'desc')->orderBy('part', 'desc')->get() as $semester)
<ul class="collapsible" @if(session()->get('section') == $semester->id) class="active" @endif>
    <li @if(session()->get('section') == $semester->id) class="active" @endif>
        <div class="collapsible-header">
                <b>{{$semester->tag}}</b>
        </div>
        <div class="collapsible-body">
            <div>
                <b>
                    @lang('anonymous_questions.number_of_fillers'):
                    {{$semester->answerSheets->count()}}
                </b>
                <x-input.button :href="route('anonymous_questions.export_answer_sheets', $semester)"
                    class="right" :text="__('anonymous_questions.export')" />
            </div>

            <ul>
                @foreach($semester->questions as $question)
                <li>
                    {{$question->title}}
                    @if($question->has_long_answers)
                    (@lang('anonymous_questions.has_long_answers'))
                    @else
                    @if($question->isMultipleChoice()) (@lang('anonymous_questions.is_multiple_choice')) @endif
                    <ul>
                        @foreach($question->options as $option)
                        <li>
                            {{$option->title}}:
                            {{$option->votes}}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @endforeach
            </ul>

            @if(!$semester->isClosed())
            <x-input.button :href="route('anonymous_questions.create', $semester)"
                    class="right green" :text="__('anonymous_questions.create_question')" />
            @endif
        </div>
    </li>
</ul>
@endforeach

@endsection
