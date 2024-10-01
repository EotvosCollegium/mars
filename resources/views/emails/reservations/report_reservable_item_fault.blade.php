@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter }} az imént jelezte, hogy
        a(z) {{ $item->name }} nevű
        {{ 
            $item->isWashingMachine()
            ? 'mosógép'
            : 'terem' }}
        @if($item->out_of_order)
        javításra került.
        @elseif($item->isWashingMachine())
        elromlott.
        @else
        használhatatlannak tűnik.
        @endif
    </p>
    <p>A probléma részletes leírása:</p>
    <blockquote>{{ $message }}</blockquote>
    <p>
        Kérjük, ellenőrizze, hogy valóban ez-e a helyzet;
        ha igen, <a href="{{ route('reservations.items.show', ['item' => $item]) }}">jelölje meg itt</a>.
    </p>
@endcomponent
