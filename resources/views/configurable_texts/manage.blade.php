@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('general.admin')</a>
    <a href="#!" class="breadcrumb">@lang('configurable_texts.configurable_texts')</a>
@endsection
@section('admin_module')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row">
                        <blockquote>
                            {{__("configurable_texts.introduction")}}
                        </blockquote>
                        <form method="POST" action="{{ route('configurable_texts.store') }}">
                            @csrf
                            @foreach($text_fields as $text_field)
                                <x-input.textarea
                                    :id="$text_field->summary()"
                                    :text="__('configurable_texts.fields.' . $text_field->key).($text_field->workshop ? ' ('.$text_field->workshop?->name.')':'')"
                                    :value="$text_field->raw_value"
                                />
                            @endforeach
                            <div class="right-align">
                                <button type="submit" class="btn waves-effect waves-light">
                                    @lang('general.save')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
