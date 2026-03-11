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
        @if($isForSecretariat)
        <p>
            <strong>Kérjük, hogy a titkárságtól csak
                az alábbi tevékenységekhez kérj igazolást:</strong>
        </p>
        <ul class="browser-default">
            <li>... <span style="color: red;">(ezt beszéljük majd meg pontosan)</span></li>
            <li>...</li>
            <li>...</li>
        </ul>
        <p>
            Ellenkező esetben a <em>Közösségi tevékenységek</em> fül alatt,
            a megfelelő választmányi taghoz nyisd le a kérelmedet,
            mert csak ők tudják ellenőrizni, hogy helyes-e.
        </p>
        <blockquote>
            Kérjük, olyan formában add meg a leírást és a dátumot,
            ahogy szeretnéd, hogy a papírra kerüljön <br/>
            (pl. <i>"Előadás 'A dehoppanálás kreatív alkamazásai' címmel a XXVII. Eötvös Konferencián"</i>
            és <i>"2026. április 24–25.".</i>).
            A titkárság e-mailben fog értesítést kapni a kérelemről.
        </blockquote>
        @else
        <blockquote>
            A választott jóváhagyó e-mailben fog értesítést kapni a kérelemről.
        </blockquote>
        @endif
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