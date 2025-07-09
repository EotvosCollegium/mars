@php
    $countries = require base_path('countries.php');
@endphp

<form method="POST" action="{{ route('users.update.personal', ['user' => $user]) }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        @can("see", [$user->personalInformation, "name"])
            <x-input.text
                id="name"
                :text="__('user.name').(user()->can('submitApplicationWithout', [$user->personalInformation, 'name'])?'':' *')"
                :value="$user->name"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'name'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'name'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "email"])
            <x-input.text
                id="email"
                type="email"
                :text="__('user.email').(user()->can('submitApplicationWithout', [$user->personalInformation, 'email'])?'':' *')"
                :value="$user->email"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'email'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'email'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "place_of_birth"])
            <x-input.text
                l=6
                id='place_of_birth'
                :text="__('user.place_of_birth').(user()->can('submitApplicationWithout', [$user->personalInformation, 'place_of_birth'])?'':' *')"
                :value="$user->personalInformation?->place_of_birth"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'place_of_birth'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'place_of_birth'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "date_of_birth"])
            <x-input.datepicker
                l=6
                id='date_of_birth'
                :text="__('user.date_of_birth').(user()->can('submitApplicationWithout', [$user->personalInformation, 'date_of_birth'])?'':' *')"
                :value="$user->personalInformation?->date_of_birth"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'date_of_birth'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'date_of_birth'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "mothers_name"])
            <x-input.text
                id='mothers_name'
                :text="__('user.mothers_name').(user()->can('submitApplicationWithout', [$user->personalInformation, 'mothers_name'])?'':' *')"
                :value="$user->personalInformation?->mothers_name"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'mothers_name'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'mothers_name'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "phone_number"])
            <x-input.text
                id='phone_number'
                type='tel'
                pattern="[+][0-9]{1,4}[-\s()0-9]*"
                minlength="8"
                maxlength="18"
                :text="__('user.phone_number').(user()->can('submitApplicationWithout', [$user->personalInformation, 'phone_number'])?'':' *')"
                helper='+36 (20) 123-4567'
                :value="$user->personalInformation?->phone_number"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'phone_number'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'phone_number'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "country"])
            <x-input.select
                id="country"
                :elements="$countries"
                :text="__('user.country').(user()->can('submitApplicationWithout', [$user->personalInformation, 'country'])?'':' *')"
                default="Hungary"
                :value="$user->personalInformation?->country"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'country'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'country'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "county"])
            <x-input.text
                l=6 id='county'
                :text="__('user.county').(user()->can('submitApplicationWithout', [$user->personalInformation, 'county'])?'':' *')"
                :value="$user->personalInformation?->county"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'county'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'county'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "zip_code"])
            <x-input.text
                l=6
                id='zip_code'
                :text="__('user.zip_code').(user()->can('submitApplicationWithout', [$user->personalInformation, 'zip_code'])?'':' *')"
                type='number'
                :value="$user->personalInformation?->zip_code"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'zip_code'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'zip_code'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "city"])
            <x-input.text
                id='city'
                :text="__('user.city').(user()->can('submitApplicationWithout', [$user->personalInformation, 'city'])?'':' *')"
                :value="$user->personalInformation?->city"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'city'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'city'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "street_and_number"])
            <x-input.text
                id='street_and_number'
                :text="__('user.street_and_number').(user()->can('submitApplicationWithout', [$user->personalInformation, 'street_and_number'])?'':' *')"
                :value="$user->personalInformation?->street_and_number"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'street_and_number'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'street_and_number'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "relatives_contact_data"])
            <x-input.text
                id='relatives_contact_data'
                :text="__('user.relatives_contact_data').(user()->can('submitApplicationWithout', [$user->personalInformation, 'relatives_contact_data'])?'':' *')"
                :helper="__('user.relatives_contact_data_desc')"
                :value="$user->personalInformation?->relatives_contact_data"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'relatives_contact_data'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'relatives_contact_data'])"
            />
        @endcan
        @can("see", [$user->personalInformation, "tenant_until"])
            <x-input.datepicker
                id='tenant_until'
                required
                :text="__('user.tenant_until').(user()->can('submitApplicationWithout', [$user->personalInformation, 'tenant_until'])?'':' *')"
                :helper="'('.__('user.in_case_of_tenants').')'"
                :value="$user->personalInformation?->tenant_until"
                :disabled="user()->cannot('edit', [$user->personalInformation, 'tenant_until'])"
                :required="user()->cannot('saveWithout', [$user->personalInformation, 'tenant_until'])"
            />
        @endcan
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
