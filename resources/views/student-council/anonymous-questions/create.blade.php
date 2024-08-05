@extends('layouts.app')

@section('title')
<a href="{{route('anonymous_questions.index_semesters')}}" class="breadcrumb" style="cursor: pointer">@lang('anonymous_questions.anonymous_questions')</a>
<a href="{{route('anonymous_questions.index_semesters')}}" class="breadcrumb" style="cursor: pointer">{{ $semester->tag }}</a>
<a href="#!" class="breadcrumb">@lang('anonymous_questions.create_question')</a>

@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{route('anonymous_questions.store', ['semester' => $semester])}}" method="POST">
                @csrf

                @include('utils.question_card', ['canHaveLongAnswers' => true])

                <div class="card-action right-align">
                    <a href="{{route('anonymous_questions.index', $semester)}}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

