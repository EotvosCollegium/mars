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
            Elfogad
        @endcomponent
    </div>
    <p>
        A regisztrációs link 7 napon belül lejár. Amennyiben addig nem használta, az alábbi gombbal kérhet új linket.
    </p>
    <div class="row">
        @component('mail::button', ['url'=> config('app.url').'/password/reset/'])
            Új link kérése
        @endcomponent
    </div>
    <p>A rendszergazdák</p>
@endcomponent
