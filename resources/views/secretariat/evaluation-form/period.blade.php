@can('manage', \App\Models\SemesterEvaluation::class)
    <div class="card">
        <form action="{{route('secretariat.evaluation.period.update')}}" method="POST">
            @csrf
            <div class="card-content">
                <div class="card-title">
                    Értékelés időszaka
                </div>
                @if($periodicEvent?->isActive())
                @endif
                <div class="row">
                    <!-- These are using html datetime-local attribute because we don't have datetime picker. The labels are not compatible with our components. -->
                    <x-input.select m="4" id="semester_id" :elements="\App\Models\Semester::all()" :value="$periodicEvent?->semester_id" :default="\App\Models\Semester::current()->id" helper="Szemeszter"/>
                    <x-input.text m="4" id="start_date" type="datetime-local" without-label helper="Megnyitás" :value="$periodicEvent?->start_date ?? now()->format('Y-m-d H:i')"/>
                    <x-input.text m="4" id="end_date" type="datetime-local" without-label helper="Határidő" :value="$periodicEvent?->end_date"/>
                </div>
                @if($periodicEvent?->isActive())
                    <blockquote>
                        <p>A félév végi értékelés jelenleg aktív, a kitöltések fogadására kész.</p>
                        <p>Az alábbi események automatikusan be vannak ütemezve (naponta egyszer futnak le, nem rögtön):</p>
                        <ul>
                            <li>Email a kérdőív megnyitásáról ({{ $periodicEvent->start_handled ?? 'még nem futott le' }})</li>
                            <li>Email emlékeztető a lejárat előtt (utolsó 3 nap)</li>
                            <li>Email az eredményekről a műhelyvezetőknek, titkárságnak, elnöknek  ({{ $periodicEvent->end_handled ?? 'még nem futott le' }})</li>
                            <li>Collegisták deaktiválása, aki nem adták meg a státuszukat a következő félévre ({{ $periodicEvent->end_handled ?? 'még nem futott le' }})</li>
                        </ul>
                    </blockquote>
                @endif
                <x-input.button floating class="right" icon="save"/>
            </div>
        </form>
    </div>
@endcan
