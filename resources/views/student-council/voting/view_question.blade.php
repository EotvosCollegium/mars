@extends('layouts.app')

@section('title')
<a href="{{route('sittings.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{route('sittings.show', $question->sitting->id)}}" class="breadcrumb" style="cursor: pointer">{{ $question->sitting->title }}</a>
<a href="#!" class="breadcrumb">{{ $question->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    @can('vote', $question)
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('questions.votes.store', $question->id)}}">
                @csrf
                <div class="card-content">
                    <span class="card-title">
                        {{ $question->title }}
                        <span class="right">
                            @livewire('passcode')
                        </span>
                    </span>
                    <blockquote>@lang('voting.description')</blockquote>
                    <blockquote class="error">@lang('voting.warning')</blockquote>
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
                    <div class="row">
                        <x-input.text id="passcode" type="password" text="voting.passcode" required />
                    </div>
                </div>
                <div class="card-action row">
                    <x-input.button class="right" text="voting.vote"/>

                </div>
            </form>
        </div>
    </div>
    @endcan
    @can('viewResults', $question)
    <div class="col s12">
        <ul class="collapsible">
            <li @if($question->isClosed()) class="active" @endif>
                <div class="collapsible-header">
                    <b>@lang('voting.results')</b>
                </div>
                <div class="collapsible-body">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ $question->title }}</th>
                                <th>{{ $question->users()->count() }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($question->options as $option)
                            <tr>
                                <td>{{$option->title}}</td>
                                <td><b>{{$option->votes}}</b></td>
                            </tr>
                            @endforeach   
                        </tbody>
                    </table>
                    <blockquote>
                        <b>Szavaztak:</b>
                        <ul>
                        @foreach($question->users()->orderBy('name')->get() as $user)
                            <li>{{$user->name}}</li>
                        @endforeach
                        </ul>
                    </blockquote>
                    @if($question->isOpen())
                        @can('administer', \App\Models\Voting\Sitting::class)
                        <form action="{{ route('questions.close', $question->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <x-input.button only-input text="voting.close_question" class="red" />
                        </form>
                        @endcan
                    @endif
                </div>
            </li>
        </ul>
    </div>
    @endcan
</div>
@endsection