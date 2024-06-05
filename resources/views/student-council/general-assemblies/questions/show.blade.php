@extends('layouts.app')

@section('title')
<a href="{{route('general_assemblies.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{route('general_assemblies.show', $question->parent->id)}}" class="breadcrumb" style="cursor: pointer">{{ $question->parent->title }}</a>
<a href="#!" class="breadcrumb">{{ $question->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    @can('vote', $question)
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('general_assemblies.questions.votes.store', [
                "general_assembly" => $question->parent->id,
                "question" => $question->id,
            ])}}">
                @csrf
                <div class="card-content">
                    <span class="card-title">
                        {{ $question->title }}
                        <span class="right">
                            @livewire('passcode', ['isFullscreen' => false])
                        </span>
                    </span>
                    <blockquote>@lang('voting.description')</blockquote>
                    <blockquote class="error">@lang('voting.warning')</blockquote>
                    <blockquote>@lang('voting.max_options') {{$question->max_options}}</blockquote>
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
                        <x-input.text id="passcode" text="voting.passcode" required />
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
            <li @if(!$question->isOpen()) class="active" @endif>
                <div class="collapsible-header">
                    @if($question->hasBeenOpened())
                    <b>@lang('voting.results')</b>
                    @else
                    <b>@lang('voting.open_question')</b>
                    @endif
                </div>
                <div class="collapsible-body">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ $question->title }}</th>
                                @if($question->isClosed())
                                <th>{{ $question->users()->count() }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($question->options->sortByDesc('votes') as $option)
                            <tr>
                                <td>{{$option->title}}</td>
                                @if($question->hasBeenOpened())
                                <td><b>{{$option->votes}}</b></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($question->isClosed())
                    <blockquote>
                        <b>Szavaztak:</b>
                        <ul>
                        @foreach($question->users()->orderBy('name')->get() as $user)
                            <li>{{$user->uniqueName}}</li>
                        @endforeach
                        </ul>
                    </blockquote>
                    @endif
                    @can('administer', \App\Models\GeneralAssemblies\GeneralAssembly::class)
                        @if($question->isOpen())
                        <p>
                            <form action="{{ route('general_assemblies.questions.close', [
                                "general_assembly" => $question->parent->id,
                                "question" => $question->id,
                            ]) }}" method="POST" style="display:inline;">
                        </p>
                        @elseif(!$question->hasBeenOpened())
                            @if($question->parent->isOpen())
                            <p>
                                <form action="{{ route('general_assemblies.questions.open', [
                                "general_assembly" => $question->parent->id,
                                "question" => $question->id,
                            ]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <x-input.button only-input text="voting.open_question" class="green" />
                                </form>
                            </p>
                            @else
                            <p class="red-text"><i>
                                @if($question->parent->hasBeenOpened())
                                @lang('voting.question_not_opened')
                                @else
                                @lang('voting.question_after_sitting')
                                @endif
                            </i></p>
                            @endif
                        @endif
                    @endcan
                </div>
            </li>
        </ul>
    </div>
    @endcan
</div>
@endsection
