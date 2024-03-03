@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="{{ route('epistola') }}" style="cursor: pointer" class="breadcrumb">Epistola Collegii</a>
<a href="#!" class="breadcrumb">Szerkesztés</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12 l8 push-l2">
        <div class="card">
            <div class="card-content">
                @if($epistola)
                <form method="POST" action="{{ route('epistola.delete', ['epistola' => $epistola->id]) }}">
                    @csrf
                    <x-input.button floating class="right red" style="margin-right: 10px" icon="delete" />
                </form>
                <form method="POST" action="{{ route('epistola.mark_as_sent', ['epistola' => $epistola->id]) }}">
                    @csrf
                    <x-input.button floating class="right green" style="margin-right: 10px" icon="mark_email_read"/>
                </form>
                @endif
                <div class="card-title">{{ $epistola->title ?? "Új hír"}}</div>

                <form method="POST" action="{{ route('epistola.update_or_create') }}" enctype="multipart/form-data">
                    @csrf
                    @if($epistola)
                    <input type="hidden" name="id" value="{{$epistola->id}}">
                    @endif
                    <div class="row">
                        <x-input.textarea id="title" text="Cím*" required>{{ $epistola ? $epistola->title : null }}</x-input.textarea>
                        <x-input.textarea id="subtitle" text="Alcím*" required>{{ $epistola ? $epistola->subtitle : null }}</x-input.textarea>
                        <x-input.textarea id="description" text="Leírás*" required>{{ $epistola ? $epistola->description : null }}</x-input.textarea>
                        <div class="col s12">
                        <blockquote>
                            Formázásra a
                            <a href='https://www.markdownguide.org/cheat-sheet/' target='__blank'>Markdown jelölései</a>
                             használhatóak.
                        </blockquote>
                        </div>
                    </div>
                    <div class="row">
                        <x-input.datepicker l=5 id="date" :value="($epistola && $epistola->date != null ? $epistola->date->format('Y-m-d') : null)" text="Dátum (esemény kezdete)" />
                        <x-input.timepicker l=2 id="time" :value="($epistola && $epistola->time != null ? $epistola->time->format('H:i') : null)" text="Időpont" />
                        <x-input.datepicker l=5 id="end_date" :value="($epistola && $epistola->end_date != null ? $epistola->end_date->format('Y-m-d') : null)" text="Esemény vége" />
                    </div>
                    <div class="row">
                        <x-input.textarea l=6 id="details_name_1" text="További infó neve" >{{ $epistola ? $epistola->details_name_1 : null }}</x-input.textarea>
                        <x-input.textarea l=6 id="details_url_1" type="url" text="További infó url">{{ $epistola ? $epistola->details_url_1 : null }}</x-input.textarea>
                    </div>
                    <div class="row">
                        <x-input.textarea l=6 id="details_name_2" text="További infó neve">{{ $epistola ? $epistola->details_name_2 : null }}</x-input.textarea>
                        <x-input.textarea l=6 id="details_url_2" type="url" text="További infó url">{{ $epistola ? $epistola->details_url_2 : null }}</x-input.textarea>
                    </div>
                    <div class="row">
                        <x-input.textarea l=6 id="deadline_name" text="Határidő neve">{{ $epistola ? $epistola->deadline_name : null }}</x-input.textarea>
                        <x-input.datepicker l=6 id="deadline_date" :value="($epistola && $epistola->deadline_date != null ? $epistola->deadline_date->format('Y-m-d') : null)" text="Határidő" />
                    </div>
                    <div class="row">
                        <x-input.datepicker l=6 id="date_for_sorting" :value="($epistola ? $epistola->date_for_sorting : null)" text="Dátum rendezéshez"/>
                        <x-input.textarea l=6 id="category" text="Kategória">{{ $epistola ? $epistola->category : null }}</x-input.textarea>
                    </div>
                    <div class="row">
                        <div class="col l6 file-field">
                            <x-input.file only_input m=6 id="picture_upload" accept=".jpg,.png" text="Kép feltöltése" />
                            <x-input.checkbox name="approved" only_input m=6 id="approved" text="Nem töltök fel szerzői jog oltalma alatt álló képet." />
                            @error('approved')
                                <blockquote>{{$message}}</blockquote>
                            @enderror
                        </div>
                        <x-input.textarea l=6 id="picture_path" type="url" text="Vagy kép linkje" >{{ $epistola ? $epistola->picture_path : null }}</x-input.textarea>
                        @if($epistola && $epistola->picture_path)
                        <img src="{{$epistola->picture_path}}" style="width: 100%">
                        @endif
                    </div>
                    @if($epistola)
                    <p>Feltöltő: {{$epistola->uploader->name}}</p>
                    @endif
                <x-input.button floating class="right" icon="send"/>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
