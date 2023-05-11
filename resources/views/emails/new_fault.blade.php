@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient }}!</h1>
<p>
    @if ($reopen === true)
        Egy hiba újra lett nyitva.
    @else
        Egy új hiba lett bejelentve.
    @endif
</p>
Részletek:
<ul>
    <li>Hiba bejelentő: {{ $fault->reporter->name }}</li>
    <li>Helyszín: {{ $fault->location }}</li>
    <li>Leírás: {{ $fault->description }}</li>
</ul>



<p>@lang('mail.administrators')</p>
@endcomponent
