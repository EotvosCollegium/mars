@component('mail::message')
    <h1>Kedves {{ $recipient }}!</h1>
    <p>
        A profilodtól a következő jogosultság eltávolításra került:
        <i>{{ $roleName }}
            @if ($objectName && $objectName != "")
                - {{ $objectName }}
            @endif
        </i>
        <br>
        Módosító: {{$modifier?->name ?? 'Automatikus'}}.
    </p>
@endcomponent
