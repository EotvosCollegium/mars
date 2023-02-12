@can('modify', \App\Models\PrintAccount::class)
<span class="card-title">Egyenleg módosítésa</span>
<blockquote>A tranzakció az admin kasszába fog kerülni.</blockquote>
<div class="row">
<form method="POST" action="{{ route('print.modify') }}">
        @csrf
        <x-input.select l=5 id="user_id_modify" text="general.user" :elements="$users" :formatter="function($user) { return $user->uniqueName; }"/>
        <x-input.text l=5 id="balance" type="number" required text="Összeg"/>
        <x-input.button l=2 class="right" text="hozzáadás"/>
    </form>
</div>
@endif
