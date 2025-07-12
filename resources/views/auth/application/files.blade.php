@extends('auth.application.app')

@section('form')

    @include('utils.user.profile-picture', ['user' => $user])

    {{-- uploaded files --}}
    <div class="card">
        <div class="card-content">
            <div class="card-title">Feltöltött fájlok</div>
            <blockquote>
                <div class="markdown_with_red">
                    @markdown(\App\Models\ConfigurableText::getText("APPLICATION_FILES"))
                </div>
            </blockquote>
            <form method="POST" action="{{ route('application.store', ['page' => 'files']) }}"
                  enctype='multipart/form-data'>
                @csrf
                <div class="row">
                    <x-input.file s=12 m=6 id="file" accept=".pdf,.jpg,.png,.jpeg" text="Fájl kiválasztása"
                        helper=".pdf,.jpg,.png,.jpeg fájlok tölthetőek fel, maximum {{config('custom.general_file_size_limit')/1000}} MB-os méretig." required
                        asterisk
                        />
                    <x-input.text s=12 m=6 id="name" text="Fájl megnevezése" maxlength="250" required
                        asterisk
                    />
                    <x-input.button only_input class="right" text="general.upload"/>
                </div>
            </form>
        </div>
    </div>
    @forelse ($user->application->files ?? [] as $file)
    <div class="card" style="padding: 10px 20px 10px 20px">
        <div class="card-title">
            <form method="POST"
                  action="{{ route('application.store', ['page' => 'files.delete', 'id' => $file->id]) }}"
                  enctype='multipart/form-data'>
                @csrf
                    <a href="{{ url($file->path) }}" target="_blank">{{ $file->name }}</a>
                    <x-input.button floating class="right btn-small" icon="delete"/>
            </form>
        </div>
    </div>
    @empty
    <div class="card" style="padding: 5px 20px 5px 20px">
        <blockquote>
            Még nem töltött fel egy fájlt sem.
        </blockquote>
    </div>
    @endforelse

@endsection
