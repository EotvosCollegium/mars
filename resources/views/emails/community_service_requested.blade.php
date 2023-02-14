@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        {{ $requester->name }} új közösségi tevékenységet hozott létre a következő leírással: <br>
        "{{ $description }}"<br>
        Kérlek fogadd el vagy utasítsd el a tevékenységet a következő linken a táblázatban: <br>
        @component('mail::button', ['url'=> route('community_service')])
            Közösségi tevékenység    
        @endcomponent
    </p>
    <p>
        Siess, mert csak két hét áll rendelkezésedre!
    </p>

    @lang('mail.administrators')
@endcomponent
