@extends('layouts.app')

@section('title')
<a href="{{route('sittings.index')}}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="{{route('sittings.show', $question->sitting->id)}}" class="breadcrumb" style="cursor: pointer">{{ $question->sitting->title }}</a>
<a href="{{route('questions.show', $question->id)}}" class="breadcrumb" style="cursor: pointer">{{ $question->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.voting')</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('questions.votes.store', $question->id)}}">
                @csrf
                <div class="card-content">
                    <span class="card-title">{{ $question->title }}</span>
                    <blockquote class="error">@lang('voting.warning')</blockquote>
                    <p style="margin-bottom:10px"><label style="font-size: 1em">@lang('voting.options')</label></p>
                    <div class="row">
                    @foreach($question->options()->get() as $option)
                        @if($question->max_options==1)
                        <x-input.radio name="option" value="{{$option->id}}" text="{{$option->title}}" />
                        @else
                        <x-input.checkbox name="option[]" value="{{$option->id}}" text="{{$option->title}}" />
                        @endif
                    @endforeach   
                    @foreach ($errors->all() as $error)
                        <blockquote class="error">{{ $error }}</blockquote>
                    @endforeach
                    </div>
                </div>
                <div class="card-action">
                    <div class="row" style="margin-bottom: 0">
                        <x-input.button only_input class="right" text="general.save"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection