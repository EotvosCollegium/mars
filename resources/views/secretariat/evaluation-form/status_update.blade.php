<div class="card">
    <div class="card-content">
        <span class="card-title">Nyilatkozz a következő félévedről ({{$periodicEvent->semester->succ()->tag}})!</span>
        @if(user()->hasRole(App\Models\Role::ALUMNI))
        <blockquote>
            A beállított státuszod: <span class="coli-text text-blue">alumni</span>.
            Ha ez véletlen lenne, akkor keresd fel a titkárságot!
        </blockquote>
        @else
        <form action="{{ route('secretariat.evaluation.store') }}" method="post">
            @csrf
            <input type="hidden" name="section" value="status" />
            @if(user()->isResident())
            <blockquote>A jelenlegi bentlakási státuszod: <span class="coli-text text-blue">bentlakó</span>.</blockquote>
            <div class="row">
                <x-input.checkbox s=12 id="resign_residency" text="A továbbiakban lemondok bentlakó helyemről, bejáró leszek." />
            </div>
            @else
            <blockquote>A jelenlegi bentlakási státuszod: <span class="coli-text text-blue">bejáró</span>.</blockquote>
            @endif
            <ul style="margin-left:20px;margin-top:0">
                <li style="list-style-type: circle !important">Aktív - Aktív tagja leszel a Collegiumnak.</li>
                <li style="list-style-type: circle !important">Passzív - Külföldi féléven leszel vagy passzív vagy az egyetemen. A collegista státuszod megmarad, de a kötelezettségeid szünetelnek (pl. óralátogatás). Ha bentlakó vagy, a helyed ideiglenesen megszűnik.</li>
                <li style="list-style-type: circle !important">Alumni - Kilépsz a Collegiumból, vagy megszűnik a hallgatói jogviszonyod - figyelem: győződj meg róla, hogy minden mást kitöltöttél, mielőtt alumnivá állítod magad!</li>
            </ul>
            Egyéb esetben írj kérvényt az igazgatónak, és jelöld be a célként kitűzött státuszt.
            <div class="row">
                <x-input.select xl=6 without_label id="next_status" required
                    :elements="[
                        \App\Models\SemesterStatus::ACTIVE,
                        \App\Models\SemesterStatus::PASSIVE,
                        \App\Models\Role::ALUMNI]"
                    :value="$evaluation?->next_status"
                    :formatter="function($o) { return __('user.'.$o); }"
                    placeholder="Tagsági státusz"/>
                <x-input.text xl=6 id="next_status_note"
                    :value="$evaluation?->next_status_note"
                    placeholder="Rövid megjegyzés: BB/Erasmus/két képzés között/stb."
                    maxlength="20"/>
                <x-input.checkbox s=12 id="will_write_request"
                    :checked="$evaluation?->will_write_request ?? false"
                    text="Írok kérvényt." />
            </div>
            <div class="row">
                <x-input.button class="right" text="general.save" />
            </div>
        </form>
        @endif
    </div>
</div>
