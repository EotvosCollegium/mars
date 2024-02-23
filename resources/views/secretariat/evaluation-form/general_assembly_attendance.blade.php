<p>Legutolsó 2 Közgyűlés:</p>
<table>
    <tbody>
    @foreach ($general_assemblies as $general_assembly)
        <tr>
            <td>{{ $general_assembly->title}}</td>
            <td>{{ $general_assembly->opened_at->format('Y-m-d') }}</td>
            <td>
                @if($general_assembly->isAttended($user))
                    <span class="green-text">Részt vettél</span>
                @else
                    <span class="red-text">Nem vettél részt</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@if($general_assemblies->count() < 2)
    <i>Még nincs legalább 2 közgyűlés a rendszerben, ezért jelezd a részvételed a megjegyzésben. A bevallást
        ellenőrizhetjük.</i>
@endif
<blockquote>
    <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">CTSZK 7. § (5) d.</a> A Collegiumból elbocsátható az
    a hallgató, aki a CHÖK két egymást követő Közgyűlésétől igazolatlanul távol marad.*<br>
    *A rendszerben résztvevőnek számít az, aki a Közgyűlésen a megfelelő számban teljesíti a jelenlét-ellenőrzéseket.
</blockquote>
<p>A követelményeket
    @if(\App\Models\GeneralAssemblies\GeneralAssembly::requirementsPassed($user))
        <span class="green-text"> teljesítetted</span>.
    @else
        <span class="red-text"> nem teljesítetted</span>.
    @endif
</p>
<form method="POST" action="">
    @csrf
    <div class="row">
        <input type="hidden" name="section" value="general_assembly"/>
        <x-input.text l=10 id="general_assembly_note" :value="$evaluation?->general_assembly_note"
                      text="Megjegyzés, helyesbítés, igazolt hiányzás"/>
        <x-input.button l=2 class="right" text="general.save"/>
    </div>
</form>
