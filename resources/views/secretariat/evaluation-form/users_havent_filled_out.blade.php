@can('manage', \App\Models\SemesterEvaluation::class)
    <div class="card">
        <div class="card-content">
            <div class="arrow-dropdown">
                <h5 class="arrow-dropdown-title closed"><a>
                        Collegisták, akik még nem töltötték ki a státuszukat
                    </a></h5>
                <div class="arrow-dropdown-content">
                    <ul>
                        @foreach($users_havent_filled_out as $user)
                            <li>{{ $user->uniqueName }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endcan
