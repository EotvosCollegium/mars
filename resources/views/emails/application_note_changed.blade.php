@component('mail::message')
    <h1>Kedves Felvételiztető!</h1>
    <p>
        {{ $modifier->name }} módosította {{ $application->user->name }} felvételi adatlapjához tartozó megjegyzést.
    </p>
    <p>
        @if($oldValue)
        Régi érték: {{ $oldValue }} </br>
        Új érték: {{ $application->note }}
        @else
        Megjegyzés: {{ $application->note }}
        @endif
    </p>
    @component('mail::button', ['url'=>  route('admission.applicants.show', ['application' => $application->id])])
        Adatlap megtekintése
    @endcomponent
@endcomponent
