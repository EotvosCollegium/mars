@component('mail::message')
    <h1>Kedves {{ $recipient->name }}!</h1>
    <p>
        Meghívtak az Eötvös Collegium tanulmányi rendszerébe, az Uránba. Itt férhet hozzá a felvételihez, a tanulmányi
        adatokhoz és egyebekhez.
    </p>
    <p>
        A hozzáféréshez egy jelszót kell megadni; ezután tud belépni a rendszerbe.
    </p>
    <div class="row">
        @component('mail::button', ['url'=> config('app.url').'/password/reset/'. $token . '?email='.$recipient->email])
            Jelszó megadása
        @endcomponent
    </div>
    <p>
        A regisztrációs link 7 napon belül lejár. Amennyiben addig nem használta,
        <a href="{{config('app.url').'/password/reset/'}}">itt kérhet új linket.</a>
    </p>
@endcomponent
