@extends('layouts.app')

@section('title')
<i class="material-icons left">chevron_right</i>@lang('issue.report')
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('issue.report')</span>
                <p>@lang('issue.report_long_description')</p>
                <form method="POST" action="{{ route('issues.store') }}">
                    @csrf
                    <div class="row">
                        <x-input.text id="title" required text="general.title" s=12 m=6 />
                        <x-input.select id="type" text="issue.select_type" s=12 m=6 without-placeholder
                                        :elements="['issue.type_bug', 'issue.type_feature']" default="issue.type_bug"
                                        :formatter="function($e) { return __($e); }"
                        />
                        <x-input.textarea id="description" required text="general.description" s=12 />
                    </div>
                    <x-input.button class="right" text="general.send"/>
                </form>
            </div>
        </div>
    </div>
    @if($url ?? '')
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('general.successfully_added')</span>
                <blockquote>
                    <a href="{{$url ?? ''}}" target="_blank" >@lang('issue.view')</a>
                </blockquote>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
