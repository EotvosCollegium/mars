@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        {{ $approver->name }} jóváhagyta a közösségi tevékenységedet: <br>
        "{{ $description }}"
    </p>

    @lang('mail.administrators')
@endcomponent
