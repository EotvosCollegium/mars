@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter }} az imént jelezte, hogy
        a(z) {{ $item->name }} nevű
        {{ 
            \App\Models\ReservableItem::WASHING_MACHINE == $item->type
            ? 'mosógép'
            : 'terem' }}
        @if($item->out_of_order)
        javításra került.
        @elseif(\App\Models\ReservableItem::WASHING_MACHINE == $item->type)
        elromlott.
        @else
        használhatatlannak tűnik.
        @endif
        Kérjük, ellenőrizze, hogy valóban ez-e a helyzet;
        ha igen, <a href="{{ route('reservations.items.show', ['item' => $item]) }}">jelölje meg itt</a>.
    </p>
@endcomponent
