@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        A szemeszter végi értékelő form eredményei letölthetőek a
        <a href="{{ route('users.index') }}">@lang("general.users")</a> menüpont alatt.
        A táblázatban az "értékelés" fül alatt találhatóak az értékek.
    </p>
    @if($deactivated)
        <p>
            Az alábbi collegisták nem töltötték ki a formot teljesen, így a rendszer automatikusan alumnivá állította
            őket.
        </p>
        <ul>
            @foreach($deactivated as $user)
                <li>{{ $user }}</li>
            @endforeach
        </ul>
        <p>
            Igény esetén státuszt vissza lehet állítani a felhasználó profilján, ám ekkor gondoskodni kell a
            profiladatok frissítéséről és a helyes státusz beállításáról.
        </p>
    @endif
@endcomponent
