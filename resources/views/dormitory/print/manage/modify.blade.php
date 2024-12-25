@can('modify', \App\Models\PrintAccount::class)
<span class="card-title">Egyenleg módosítása</span>
<blockquote>A tranzakció az admin kasszába fog kerülni.</blockquote>
<div class="row">
<form method="POST" action="{{ route('print.print-account.update') }}">
        @csrf
        @method('PUT')
        <x-input.select l=5 id="user" text="general.user" :elements="$users" :formatter="fn($user) => $user->uniqueName"/>
        <x-input.text l=5 id="amount" type="number" required text="Összeg"/>
        <x-input.button l=2 class="right" text="hozzáadás"/>
    </form>
</div>
@endif
