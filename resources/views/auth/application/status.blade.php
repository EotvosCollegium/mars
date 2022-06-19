@switch($status)
    @case(App\Models\ApplicationForm::STATUS_IN_PROGRESS)
        <i class="coli-text text-orange">Folyamatban</i>
        @break
    @case(App\Models\ApplicationForm::STATUS_SUBMITTED)
        <i class="green-text">Véglegesítve</i>
        @break
    @case(App\Models\ApplicationForm::STATUS_BANISHED)
        @if($admin ?? false)
            <i class="red-text">Elutasítva</i>
        @else
            <i class="green-text">Véglegesítve</i>
        @endif
        @break
    @default
        <i>Ismeretlen</i>
@endswitch
