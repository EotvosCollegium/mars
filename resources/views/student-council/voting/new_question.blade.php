@extends('layouts.app')

@section('title')
<a href="{{route('voting')}}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="{{ route('voting.view_sitting', $sitting)}}" class=breadcrumb>{{ $sitting->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.new_question')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('voting.add_question', $sitting) }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">@lang('voting.new_question')</span>
                    <div class="row">
                        <x-input.text s="12" type="text" text="{{ __('voting.question_title') }}" id="title" maxlength="100" required/>
                    </div>
                    <div class="row">
                        {{-- TODO: this should be done in a less ugly way --}}
                        <x-input.textarea id="options" s="12" l="10" text="{{ __('voting.options_instructions') }}" required/>
                        <x-input.text type="number" min="1" max="3" value="1" s="12" l="2" id="max_options" text="{{ __('voting.max_options') }}" required/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ route('voting.view_sitting', $sitting) }}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

