@can('create', \App\Models\FreePages::class)
<span class="card-title">Ingyenes oldalak hozzáadása</span>
<div class="row">
    <form method="POST" action="{{ route('print.free_pages') }}">
        @csrf
        <x-input.select l=3 :elements="$users" :formatter="function($user) { return $user->uniqueName; }" id="user_id_free" text="general.user"/>
        <x-input.text l=3 id="free_pages" type="number" min='1' text="print.quantity" required/>
        <x-input.datepicker l=3 id="deadline" text="Lejárat" year_range=10 required/>
        <x-input.text l=3 id="comment" locale="general" required/>
        <x-input.button class="right" text="hozzáadás"/>
    </form>
@endif</div>
