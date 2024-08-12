<!-- Admin site showing one applicants -->
@extends('layouts.app')
@section('title')
    <a href="{{ route('admission.applicants.index') }}" class="breadcrumb" style="cursor: pointer">Felvételi
        jelentkezők</a>
    <a href="#!" class="breadcrumb">{{$user->name}}</a>
@endsection

@section('content')
    @include('auth.application.application', ['user' => $user, 'expanded' => true, 'admin' => $admin ?? false])
    @can('editStatus', \App\Models\Application::class)
    <div class="card">
        <form method="POST" action="{{ route('admission.applicants.update', ['application' => $user->application]) }}"
              enctype='multipart/form-data'>
            <div class="card-content">
                <div class="card-title">Utólagos fájlfeltöltés</div>
                @csrf
                <blockquote>Az új fájlról a felvételiztető bizottság tagjai értesítést kapnak.</blockquote>
                <div class="row">
                    <x-input.file s=12 m=6 id="file" accept=".pdf,.jpg,.png,.jpeg" text="general.browse"
                                  helper=".pdf,.jpg,.png,.jpeg fájlok tölthetőek fel, maximum {{config('custom.general_file_size_limit')/1000}} MB-os méretig."
                                  required/>
                    <x-input.text s=12 m=6 id="name" text="Fájl megnevezése" maxlength="250" required/>
                </div>
                <x-input.button floating class="right" icon="upload"/>
            </div>
        </form>
    </div>
    @endcan
    <div class="card">
        <form method="POST"
              action="{{route('admission.applicants.update', ['application' => $user->application->id])}}">
            @csrf
            <div class="card-content">
                <div class="card-title">Felvételizővel kapcsolatos megjegyzés</div>
                <div class="row">
                    @csrf
                    <div class="col">
                        <blockquote>A megjegyzéseket a felvételiző nem látja, de azok láthatóak a többi felvételiztető számára (akár más műhelyekből is).<br/>
                            A módosításokról a felvételiztető bizottság tagjai értesítést kapnak.</blockquote>
                    </div>
                    <x-input.textarea id="note"
                                      text="Megjegyzés"
                                      helper="pl. státusz változás, lemondás"
                                      :value="$user->application->note"/>
                </div>
                <x-input.button floating class="right" icon="save"/>
            </div>
        </form>
    </div>
@endsection
