@extends('layouts.app')

@section('title')
<a href="{{ route('voting') }}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="#!" class="breadcrumb">{{ $sitting->title }}</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $sitting->title }}
                    <!--
                    @can('update', $sitting)
                        <x-input.button :href="route('routers.edit', $router->ip)" floating class="right" icon="edit" />
                    @endcan
                    @can('delete', $sitting)
                        <form action="{{ route('routers.delete', $router->ip) }}" method="POST" class="right" style="margin-right:10px">
                            @csrf
                            <x-input.button floating icon="delete" class="red" />
                        </form>
                    @endcan
                    -->
                </span>
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
                                <form action="{{ route('voting.close_sitting', $sitting->id) }}" method="POST" class="right" style="margin-right:10px">
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
                <span class="card-title">@lang('voting.questions')
                    <!--
                    @can('update', $sitting)
                        <x-input.button :href="route('routers.edit', $router->ip)" floating class="right" icon="edit" />
                    @endcan
                    @can('delete', $sitting)
                        <form action="{{ route('routers.delete', $router->ip) }}" method="POST" class="right" style="margin-right:10px">
                            @csrf
                            <x-input.button floating icon="delete" class="red" />
                        </form>
                    @endcan
                    -->
                </span>
                <table>
                    <thead>
                    <tr>
                        <th>@lang('voting.question_title')</th>
                        <th>@lang('voting.opened_at')</th>
                        <th>@lang('voting.closed_at')</th>
                        <th></th>
                        <th>
                            @if($sitting->isOpen())
                            @can('administer', $sitting)
                            <a href="{{ route('voting.new_question', $sitting->id) }}" class="btn-floating waves-effect waves-light right">
                                <i class="material-icons">add</i>
                            </a>
                            @endcan
                            @endif
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($sitting->questions() as $question)
                    <tr>
                        <td>{{$question->title}}</td>
                        <td>{{$question->opened_at}}</td>
                        <td>{{$question->closed_at}}</td>
                        <td>
                            @if($question->isOpen())
                            @can('administer', $sitting)
                            <form action="{{ route('voting.close_question', $question->id) }}" method="POST" class="right" style="margin-right:10px">
                                @csrf
                                <x-input.button text="voting.close_question" class="red" />
                            </form>
                            @endcan
                            @endif
                        </td>
                        <td>
                            @can('vote', $question)
                            <a href="{{ route('voting.vote', $question->id) }}" class="btn-floating waves-effect waves-light right">
                                <i class="material-icons">thumbs_up_down</i>
                            </a>
                            @endcan
                        </td>
                        <td>
                            @can('view_results', $question)
                            <a href="{{ route('voting.view_question', $question->id) }}" class="btn-floating waves-effect waves-light right">
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