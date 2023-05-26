<blockquote>
    A Választmány működéséhez és fejlődéséhez elengedhetetlen a visszajelzések adása, melyet itt tudsz megtenni.<br>
    A visszajelzéseket csak a Választmány elnöke és a CHÖK titkár kapja meg, de adhatsz visszajelzést a bizottságok munkájával kapcsolatban is.
    Ezen felül bátorítunk a Közgyűléseken való felszólalásra.
</blockquote>
<form method="POST" action="{{ route('secretariat.evaluation.store') }}">
    @csrf
    <div class="row">
        <input type="hidden" name="section" value="feedback"/>
        <x-input.textarea id="feedback" :value="$evaluation?->feedback" text="Visszajelzés..." style="height:100px" />
        <x-input.checkbox id="anonymous_feedback" s=10 text="Névtelen visszajelzés" />
        <x-input.button s=2 class="right" text="Küldés" />
    </div>
</form>
<blockquote>
    A névtelen visszajelzéseket nem tároljuk, ezért a mező küldés után üres marad. Ismételt elküldésre nincs szükség.
</blockquote>
