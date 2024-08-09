@component('mail::message')
    <h1>@lang('mail.dear') Felvételiztető!</h1>
    <p>
        Utólagosan új fájl lett feltöltve {{ $application->user->name }} felvételi adatlapjához, '{{ $fileName }}' néven.
    </p>
    @component('mail::button', ['url'=>  route('admission.applicants.show', ['application' => $application->id])])
        Megtekintés
    @endcomponent
@endcomponent
