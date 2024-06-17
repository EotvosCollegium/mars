@extends('layouts.app')

@section('title')
<a href="{{route('general_assemblies.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{route('general_assemblies.show', $general_assembly)}}" class="breadcrumb" style="cursor: pointer">{{ $general_assembly->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.new_question')</a>

@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{route('general_assemblies.questions.store', ['general_assembly' => $general_assembly])}}" method="POST">
                @csrf

                @include('utils.question_card', ['canHaveLongAnswers' => false])

                <div class="card-action right-align">
                    <a href="{{route('general_assemblies.show', $general_assembly)}}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

