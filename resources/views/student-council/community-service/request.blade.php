<blockquote>A választott jóváhagyó e-mailben fog értesítést kapni a kérelemről.</blockquote>
<form method="POST" action="{{ route('community_service.create') }}">
    @csrf
    <div class="row">
        <x-input.text m=6 l=6 id="description" required text="rendezvény, feladatkör, tevékenység" />
        <x-input.select m=4 l=4 id="approver" :elements="\App\Models\User::studentCouncilLeaders()" text="jóváhagyó"/>
        <x-input.button s=2 class="right" text="Mentés" />
    </div>
</form>
