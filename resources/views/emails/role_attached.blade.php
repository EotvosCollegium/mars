
@component('mail::message')
    <h1>Kedves {{ $recipient }}!</h1>
    <p>
        Új jogosulság lett a profilodhoz rendelve:
        <i>{{ $roleName }}
            @if ($objectName && $objectName != "")
                - {{ $objectName }}
            @endif
        </i>
        <br>
        Módosító: {{$modifier?->name ?? 'Automatikus'}}.
    </p>
@endcomponent
