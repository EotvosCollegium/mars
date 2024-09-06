@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        @if($item->out_of_order)
        @lang('reservations.has_become_faulty')
        @else
        @lang('reservations.got_repaired')
        @endif
        <a href="{{
                $item->isWashingMachine()
                ? route('reservations.items.index', ['type' => \App\Enums\ReservableItemType::WASHING_MACHINE])
                : route('reservations.show', $item)
            }}">
            {{ ' ' . $item->name . '.' }}
        </a>
    </p>
    <p>
        @lang('reservations.check_reservations')
    </p>
@endcomponent
