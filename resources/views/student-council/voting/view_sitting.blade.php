@extends('layouts.app')

@section('title')
<a href="{{ route('sittings.index') }}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="#!" class="breadcrumb">{{ $sitting->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $sitting->title }}</span>
                <table>
                    <tbody>
                        <tr>
                            <th scope="row">@lang('voting.opened_at')</th>
                            <td>{{ $sitting->opened_at }}</td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('voting.closed_at')</th>
                            <td>{{ $sitting->closed_at }}</td>
                            @if($sitting->isOpen())
                            @can('administer', $sitting)
                            <td>
                                <form action="{{ route('sittings.close', $sitting->id) }}" method="POST" class="right" style="margin-right:10px">
                                    @csrf
                                    <x-input.button text="voting.close_sitting" class="red" />
                                </form>
                            </td>
                            @endcan
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.questions')</span>
                <table>
                    <thead>
                    <tr>
                        <th>@lang('voting.question_title')</th>
                        <th>@lang('voting.opened_at')</th>
                        <th>@lang('voting.closed_at')</th>
                        <th></th>
                        <th></th>
                        <th>
                            @if($sitting->isOpen())
                            @can('administer', $sitting)
                            <form action="{{ route('questions.create') }}" method="GET" class="right" style="margin-right:10px">
                                @csrf
                                <input type="hidden" name="sitting" value="{{$sitting->id}}"/>
                                <button type="submit" class="btn-floating waves-effect waves-light right">
                                    <i class="material-icons">add</i>
                                </button>
                            </form>
                            @endcan
                            @endif
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($sitting->questions()->orderByDesc('opened_at')->get() as $question)
                    <tr>
                        <td>{{$question->title}}</td>
                        <td>{{$question->opened_at}}</td>
                        <td>{{$question->closed_at}}</td>
                        <td>
                            @if($question->isOpen())
                            @can('administer', $sitting)
                            <form action="{{ route('questions.close', $question->id) }}" method="POST" class="right" style="margin-right:10px">
                                @csrf
                                <x-input.button text="voting.close_question" class="red" />
                            </form>
                            @endcan
                            @endif
                        </td>
                        <td>
                            @can('vote', $question)
                            <a href="{{ route('questions.votes.create', $question->id) }}" class="btn-floating waves-effect waves-light right">
                                <i class="material-icons">thumbs_up_down</i>
                            </a>
                            @endcan
                        </td>
                        <td>
                            @can('viewResults', $question)
                            <a href="{{ route('questions.show', $question->id) }}" class="btn-floating waves-effect waves-light right">
                                <i class="material-icons">remove_red_eye</i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection