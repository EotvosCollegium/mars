@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        A {{ $semester }} félévre a státuszod <i>{{ $status }}</i> lett.<br>
        Módosító: {{$modifier?->name??'Automatikus'}}.
        @if($comment)
            <br>
            Megjegyzés: {{$comment}}
        @endif
    </p>

    @lang('mail.administrators')
@endcomponent
