@php
    $countries = require base_path('countries.php');
@endphp

<form method="POST" action="{{ route('users.update.personal', ['user' => $user]) }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <x-input.text
            id="name"
            text="user.name"
            :value="$user->name"
            required />
        <x-input.text
            id="email"
            type="email"
            text="user.email"
            :value="$user->email"
            required />
        @if (!$user->isTenant() || isset($application))
        <x-input.text
            l=6
            id='place_of_birth'
            required
            text='user.place_of_birth'
            :value="$user->personalInformation?->place_of_birth" />
        <x-input.datepicker
            l=6
            id='date_of_birth'
            required
            text='user.date_of_birth'
            :value="$user->personalInformation?->date_of_birth" />
        <x-input.text
            id='mothers_name'
            required
            text='user.mothers_name'
            :value="$user->personalInformation?->mothers_name" />
        @endif
        <x-input.text
            id='phone_number'
            type='tel'
            required
            pattern="[+][0-9]{1,4}[-\s()0-9]*"
            minlength="8"
            maxlength="18"
            text='user.phone_number'
            helper='+36 (20) 123-4567'
            :value="$user->personalInformation?->phone_number" />
        @if (!$user->isTenant() || isset($application))
        <x-input.select
            id="country"
            :elements="$countries"
            text="user.country"
            default="Hungary"
            :value="$user->personalInformation?->country" />
        <x-input.text
            l=6 id='county'
            text='user.county'
            required
            :value="$user->personalInformation?->county" />
        <x-input.text
            l=6
            id='zip_code'
            text='user.zip_code'
            type='number'
            required
            :value="$user->personalInformation?->zip_code" />
        <x-input.text
            id='city'
            text='user.city'
            required
            :value="$user->personalInformation?->city" />
        <x-input.text
            id='street_and_number'
            text='user.street_and_number'
            required
            :value="$user->personalInformation?->street_and_number" />
        <x-input.text
            id='relatives_contact_data'
            text='user.relatives_contact_data'
            :helper="__('user.relatives_contact_data_desc')"
            :value="$user->personalInformation?->relatives_contact_data" />
        @endif
        @if ($user->isTenant() && !isset($application))
            <x-input.datepicker
                id='tenant_until'
                required
                text='user.tenant_until'
                :helper="'('.__('user.in_case_of_tenants').')'"
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
