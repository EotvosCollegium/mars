@can('administrate', $checkout)
    <div class="card">
        <form action="{{route('kktnetreg.period.update')}}" method="POST">
            @csrf
            <div class="card-content">
                <div class="card-title">
                    KKT / Netreg fizetés időszak
                </div>
                @if($periodicEvent?->isActive())
                    <blockquote>Jelenleg a KKT/Netreg fizetési időszak aktív. A határidő: {{$periodicEvent?->endDate()}}. </blockquote>
                @else
                    <blockquote>Jelenleg a KKT/Netreg fizetési időszak nem aktív. Az aktiváláshoz add meg a határidőt, ami az internet hozzáférés vége lesz.</blockquote>
                    <div class="row">
                        <!-- These are using html datetime-local attribute because we don't have datetime picker. The labels are not compatible with our components. -->
                        <x-input.select m="3" id="semester_id" :elements="\App\Models\Semester::all()" :value="$periodicEvent?->semester_id" :default="\App\Models\Semester::current()->id" helper="Szemeszter"/>
                        <x-input.text m="3" id="end_date" type="datetime-local" without-label helper="Határidő" :value="$periodicEvent?->end_date"/>
                    </div>
                    <blockquote class="error">Megnyitás után a határidő nem módosítható, de a határidő lejárata után is lehet még fizetést felvinni.</blockquote>

                    <x-input.button floating class="right" icon="save"/>
                @endif
            </div>
        </form>
    </div>
@endcan
