@extends('auth.application.app')

@section('form')
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
        'extensions' => '.pdf',
        'default_name' => "Önéletrajz",
    ])

    @include('auth.application.file_upload', [
        'application' => $user->application,
        'type' => App\Enums\FileType::BESOROLASI_HATAROZAT,
        'extensions' => '.pdf',
        'default_name' =>  "Besorolási határozat",
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
