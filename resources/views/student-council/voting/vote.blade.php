@extends('layouts.app')

@section('title')
<a href="{{route('voting')}}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="{{route('voting.view_sitting', $question->sitting()->id)}}" class="breadcrumb">{{ $question->sitting()->title }}</a>
<a href="{{route('voting.view_question', $question->id)}}" class="breadcrumb">{{ $question->title }}</a>
<a href="#!" class="breadcrumb">@lang('voting.voting')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('voting.vote', $question->id)}}">
                @csrf
                <div class="card-content">
                    <span class="card-title">{{ $question->title }}</span>
                    <p class="red-text">@lang('voting.warning')</p>
                    <table>
                        <thead>
                            <tr>
                                <th>@lang('voting.options')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($question->getOptions() as $option)
                            <tr>
                                <td>{{$option->title}}</td>
                                <td>
                                    @if($question->max_options==1)
                                    <input style="opacity: 1; pointer-events: auto;" type="radio" name="option" value="{{ $option->id }}" text="{{ $option->title }}">
                                    @else
                                    <input style="opacity: 1; pointer-events: auto;" type="checkbox" name="option[]" value="{{ $option->id }}" class="filled-in checkbox-color" text="{{$option->title}}" />
                                    @endif
                                </td>
                            </tr>
                            @endforeach   
                        </tbody>
                    </table>
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