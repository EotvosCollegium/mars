@extends('auth.application.app')

@section('files-active')
    active
@endsection

@section('form')

    @include('utils.user.profile-picture', ['user' => $user])

    {{-- uploaded files --}}
    <div class="card">
        <div class="card-content">
            <div class="card-title">Feltöltött fájlok</div>
            <blockquote>
                A pályázatnak az alábbiakat kell tartalmaznia:
                <ul style="margin-left:20px;margin-top:0">
                    <li style="list-style-type: circle !important">önéletrajz (hagyományos, leíró jellegű)</li>
                    <li style="list-style-type: circle !important">elsőéves egyetemistaként:<br/>
                        szakfelvételi engedély/felvételi határozat (Neptun: Tanulmányok - Hivatalos
                        bejegyzések menüpont alatt letölthető)<br/>
                        érettségi bizonyítvány másolata
                    </li>
                    <li style="list-style-type: circle !important">lezárt egyetemi félévek esetén:<br/>
                        diploma másolata vagy leckekönyv/törzslap kivonat az eddigi eredményekről
                    </li>
                    <li style="list-style-type: circle !important">opcionális: oklevelek, igazolások, szaktanári
                        ajánlás
                    </li>
                </ul>
            </blockquote>

            <div style="margin: 0 20px 0 20px;">
                @forelse ($user->application->files ?? [] as $file)
                    @if (!$loop->first)
                        <div class="divider"></div>
                    @endif
                    <div class="row" style="margin-bottom: 0; padding: 10px">
                        <form method="POST"
                              action="{{ route('application.store', ['page' => 'files.delete', 'id' => $file->id]) }}"
                              enctype='multipart/form-data'>
                            @csrf

                            <div class="col s10" style="margin-top: 5px">
                                <a href="{{ url($file->path) }}" target="_blank">{{ $file->name }}</a>
                            </div>
                            <div class="col s2">
                                <x-input.button floating class="right btn-small" icon="delete"/>
                            </div>
                        </form>
                    </div>
                @empty
                    <p>Még nem töltött fel egy fájlt sem.</p>
                @endforelse
            </div>
        </div>
    </div>
    {{-- upload --}}
    <div class="card">
        <form method="POST" action="{{ route('application.store', ['page' => 'files']) }}"
              enctype='multipart/form-data'>
            @csrf
            <div class="card-content">
                <div class="card-title">Feltöltés</div>
                <div class="row">
                    <x-input.file s=12 m=6 id="file" size="2000000" accept=".pdf,.jpg,.png,.jpeg" text="general.browse" required/>
                    <x-input.text s=12 m=6 id="name" text="Fájl megnevezése" required/>
                </div>
                <x-input.button only_input class="right" text="general.upload"/>
                <blockquote>A feltölteni kívánt fájlok maximális mérete: 2 MB, az engedélyezett formátumok: .pdf, .jpg,
                    .jpeg, .png
                </blockquote>
            </div>
        </form>
    </div>

@endsection
