<form action="{{ route('internet.mac_addresses.add') }}" method="post">
    <div class="row">
        @csrf
        <x-input.select xl=3 :elements="$users" id="user_id" text="general.user" :formatter="function($user) { return $user->uniqueName; }"/>
        <x-input.text xl=3 id="mac_address" placeholder="00:00:00:00:00:00" required text="MAC cím" />
        <x-input.text xl=3 id="comment" placeholer="Laptop" required text="megjegyzés"/>
        <x-input.button xl=3 class="right" text="hozzáadás"/>
    </div>
</form>
