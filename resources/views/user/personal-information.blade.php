<form method="POST" action="{{ route('users.update.personal', ['user' => $user]) }}">
    @csrf
    <div class="row">
        <x-input.text
            id="name"
            locale="user"
            :value="$user->name"
            required />
        <x-input.text
            id="email"
            type="email"
            locale="user"
            :value="$user->email"
            required />
        <x-input.text
            l=6
            id='place_of_birth'
            required
            locale='user'
            :value="$user->personalInformation?->place_of_birth" />
        <x-input.datepicker
            l=6
            id='date_of_birth'
            required
            locale='user'
            :value="$user->personalInformation?->date_of_birth" />
        <x-input.text
            id='mothers_name'
            required
            locale='user'
            :value="$user->personalInformation?->mothers_name" />
        <x-input.text
            id='phone_number'
            type='tel'
            required
            pattern="[+][0-9]{1,4}[-\s()0-9]*"
            minlength="8"
            maxlength="18"
            locale='user'
            helper='+36 (20) 123-4567'
            :value="$user->personalInformation?->phone_number" />
        <x-input.select
            id="country"
            :elements="$countries"
            locale="user"
            default="Hungary"
            :value="$user->personalInformation?->country" />
        <x-input.text
            l=6 id='county'
            locale='user'
            required
            :value="$user->personalInformation?->county" />
        <x-input.text
            l=6
            id='zip_code'
            locale='user'
            type='number'
            required
            :value="$user->personalInformation?->zip_code" />
        <x-input.text
            id='city'
            locale='user'
            required
            :value="$user->personalInformation?->city" />
        <x-input.text
            id='street_and_number'
            locale='user'
            required
            :value="$user->personalInformation?->street_and_number" />
        @if ($user->hasRole(\App\Models\Role::TENANT))
            <x-input.datepicker
                id='tenant_until'
                required
                locale='user'
                :value="$user->personalInformation?->tenant_until" />
        @endif
        <x-input.button class="right" text="general.save" />
    </div>
</form>

@push('scripts')
    <script>
		$(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                firstDay: 1,
                yearRange: 50,
                //maxDate: new Date(),
            });
        });
    </script>
@endpush
