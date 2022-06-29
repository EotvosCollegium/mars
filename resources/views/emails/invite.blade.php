@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        Meghívtak az Eötvös Collegium tanulmányi rendszerébe, az Uránba.
    </p>
    <p>
        Itt férhet hozzá a felvételihez, a tanulmányi adatokhoz és egyebekhez.
    </p>
    <div class="row">
        @component('mail::button', ['url'=> config('app.url').'/password/reset/'. $token . '?email='.$recipient->email])
            @lang('user.accept')
        @endcomponent
    </div>
    <p>@lang('mail.administrators')</p>
@endcomponent
