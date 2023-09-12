@extends('layouts.app')

@section('title')
<a href="{{route('general_assemblies.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{ route('general_assemblies.show', $general_assembly)}}" class="breadcrumb" style="cursor: pointer">{{ $general_assembly->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.new_question')</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('general_assemblies.questions.store', ['general_assembly' => $general_assembly]) }}" method="POST">
                @csrf
                <div class="card-content">
                @foreach ($errors->all() as $error)
                <blockquote class="error">{{ $error }}</blockquote>
            @endforeach
                    <span class="card-title">@lang('voting.new_question')</span>
                    <div class="row">
                        <x-input.text s="12" type="text" text="voting.question_title" id="title" maxlength="100" required/>
                    </div>
                    <div class="row">
                        @livewire('parent-child-form', ['title' => __('voting.options'), 'name' => 'options', 'items' => old('options')])
                    </div>
                    <div class="row">
                        <x-input.text type="number" value="1" id="max_options" text="voting.max_options" required/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ route('general_assemblies.show', $general_assembly) }}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

