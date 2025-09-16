@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>{{ $languageExam->educationalInformation->user->name }} <a href="{{  route('users.show', $languageExam->educationalInformation->user->id) }}">profiljához</a> egy új nyelvvizsga-bizonyítvány lett feltöltve.</p>
    <ul>
        <li>Nyelv: @lang('role.' . $languageExam->language)</li>
        <li>Szint: {{ $languageExam->level ?? 'egyéb' }}</li>
        <li>Típus: {{ $languageExam->type }}</li>
        <li>Dátum: {{ $languageExam->date->format('Y-m-d') }}</li>
    </ul>
    @component('mail::button', ['url' => url($languageExam->path)])
        Bizonyítvány megtekintése
    @endcomponent
    <p>Módosító: {{$modifier?->name ?? 'Automatikus'}}.</p>
@endcomponent
