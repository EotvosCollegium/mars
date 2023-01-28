@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="{{ route('routers') }}" class="breadcrumb" style="cursor: pointer">@lang('router.router_monitor')</a>
<a href="{{ route('voting.view_sitting', $sitting)}}" class=breadcrumb>{{ $sitting->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.new_question')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('voting.add_sitting') }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">@lang('voting.new_question')</span>
                    <div class="row">
                        <x-input.text s="12" type="text" text="voting.question_title" id="title" maxlength="100" required/>
                    </div>
                    <div class="row">
                        <x-input.select s="12" id="approver" :elements="$possible_max_options" :text="__('voting.max_options')"/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ route('voting.view_sitting', $sitting) }}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('voting.save_question')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

