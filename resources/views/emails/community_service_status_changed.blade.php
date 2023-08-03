@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        {{ $approver->name }} {{$approved?'jóváhagyta':'elutasította'}} a közösségi tevékenységedet: <br>
        "{{ $description }}"
    </p>
@endcomponent
