@can('manage', \App\Models\SemesterEvaluation::class)
    <div class="card">
        <form action="{{route('secretariat.evaluation.period.update')}}" method="POST">
            @csrf
            <div class="card-content">
                <div class="card-title">
                    Értékelés időszaka
                </div>
                @if($periodicEvent?->isActive())
                    <blockquote>A félév végi értékelés jelenleg aktív, a kitöltések fogadására kész.</blockquote>
                @endif
                <div class="row">
                    <!-- These are using html datetime-local attribute because we don't have datetime picker. The labels are not compatible with our components. -->
                    <x-input.select m="4" id="semester_id" :elements="\App\Models\Semester::all()" :value="$periodicEvent?->semester_id" :default="\App\Models\Semester::current()->id" helper="Szemeszter"/>
                    <x-input.text m="4" id="start_date" type="datetime-local" without-label helper="Megnyitás" :value="$periodicEvent?->start_date ?? now()->format('Y-m-d H:i')"/>
                    <x-input.text m="4" id="end_date" type="datetime-local" without-label helper="Határidő" :value="$periodicEvent?->end_date"/>
                </div>
                <x-input.button floating class="right" icon="save"/>
            </div>
        </form>
    </div>
@endcan
