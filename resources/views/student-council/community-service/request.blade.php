<blockquote>
    <p>
        Ha igazolást is szeretnél, kérjük, olyan formában add meg a leírást és a dátumot,
        ahogy szeretnéd, hogy a papírra kerüljön <br/>
        (pl. <i>"Plakát tervezése."</i> és <i>"2026. február közepe"</i>).
    </p>
    <p>A választott jóváhagyó e-mailben fog értesítést kapni a kérelemről.</p>
</blockquote>
<form method="POST" action="{{ route('community_service.create') }}">
    @csrf
    <div class="row">
        <x-input.text m=12 id="description" required text="Rendezvény, feladatkör, tevékenység" />
        <x-input.text m=6 id="date_of_service" text="Tevékenység dátuma" />
        <x-input.select m=4 id="approver" :elements="array_merge([\App\Models\User::secretary()], \App\Models\User::studentCouncilLeaders()->toArray())" text="jóváhagyó"/>
        <x-input.button m=2 class="right" text="Mentés" />
    </div>
</form>
