@php
    $approvers = $isForSecretariat
                ? [\App\Models\User::secretary()]
                : \App\Models\User::studentCouncilLeaders()->toArray();
@endphp

<div class="card">
    <div class="card-content">
        <span class="card-title">
            @if($isForSecretariat) Új igazolás @else Új közösségi tevékenység @endif
        </span>
        <blockquote>
            @if($isForSecretariat)
            <p>
                Ha igazolást szeretnél, kérjük, olyan formában add meg a leírást és a dátumot,
                ahogy szeretnéd, hogy a papírra kerüljön <br/>
                (pl. <i>"Előadás 'A dehoppanálás kreatív alkamazásai' címmel a XXVII. Eötvös Konferencián"</i>
                és <i>"2026. április 24–25.".</i>).
            </p>
            @endif
            <p>A választott jóváhagyó e-mailben fog értesítést kapni a kérelemről.</p>
        </blockquote>
        <form method="POST" action="{{ route('community_service.create') }}">
            @csrf
            <div class="row">
                <x-input.text m=12 id="description" required text="Rendezvény, feladatkör, tevékenység" />
                <x-input.text m=6 id="date_of_service" text="Tevékenység dátuma" />
                <x-input.select m=4 id="approver"
                    :elements="$approvers"
                    text="jóváhagyó"/>
                <x-input.button m=2 class="right" text="Mentés" />
            </div>
        </form>
    </div>
</div>