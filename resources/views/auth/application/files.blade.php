@extends('auth.application.app')

@section('form')
    @include('utils.user.profile-picture', ['user' => $user])

    {{-- uploaded files --}}
    <div class="card">
        <div class="card-content">
            <div class="card-title">Feltöltött fájlok</div>
            <blockquote>
                <div class="markdown_with_red">
                    @markdown(\App\Models\ConfigurableText::getText('APPLICATION_FILES'))
                </div>
            </blockquote>
        </div>
    </div>

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::RESUME,
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::BESOROLASI_HATAROZAT,
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::ERETTSEGI,
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::ELVEGZETT_FELEV,
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::DIPLOMA,
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::APPLICATION_CUSTOM,
        'active' => true,
        'optional' => true,
    ])
@endsection
