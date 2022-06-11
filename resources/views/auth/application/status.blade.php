@switch($status)
    @case(App\Models\ApplicationForm::STATUS_IN_PROGRESS)
        <i class="coli-text text-orange">Folyamatban</i>
        @break
    @case(App\Models\ApplicationForm::STATUS_SUBMITTED)
        <i class="green-text">Véglegesítve</i>
        @break
    @case(App\Models\ApplicationForm::STATUS_BANISHED)
        <i class="green-text">Véglegesítve</i>
        @break
    @default
        <i>Ismeretlen</i>
@endswitch
