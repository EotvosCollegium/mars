@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter}} egy internetes hibát jelentett be.
    </p>
    <ul>
        <li>Leírás: {{ $report }}</li>
        <li>Hibaüzenet: {{ $error_message }}</li>
        <li>Mikor jelent meg a hiba: {{ $when }}</li>
        <li>Mit próbált eddig: {{ $tries }}</li>
        <li>Operációs rendszer: {{ $user_os }}</li>
        <li>Szoba: {{ $room }}</li>
        <li>Elérhetőség: {{ $availability }}</li>
        <li>Engedély belépni a szobába: {{ $can_enter ? "Igen" : "Nem"}}</li>
    </ul>
@endcomponent
