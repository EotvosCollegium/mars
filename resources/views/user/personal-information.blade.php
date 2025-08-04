@php
    $countries = require base_path('countries.php');
@endphp

<form method="POST" action="{{ route('users.update.personal', ['user' => $user]) }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        @if (user()->cannot('edit', $user))
            @markdown(__('user.data_cannot_be_edited_application'),
                [
                    'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                    'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                    'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
                ]
            )
        @elseif(user()->cannot('editStaticPersonalInformation', $user))
            @markdown(__('user.some_data_cannot_be_edited'),
                [
                    'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                    'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                    'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
                ]
            )
        @endif
        <x-input.text
            id="name"
            autocomplete="name"
            text="user.full_name"
            :value="$user->name"
            required
            :disabled="user()->cannot('editStaticPersonalInformation', $user)"
            asterisk
            maxlength="255"
            />
        <x-input.text
            id="email"
            type="email"
            autocomplete="email"
            text="user.email"
            :value="$user->email"
            required
            :disabled="user()->cannot('editStaticPersonalInformation', $user)"
            asterisk
            maxlength="225"
            />
        @if (!$user->isTenant() || $user->hasRoleOtherThanTenant() || isset($application))
        <x-input.text
            l=6
            id='place_of_birth'
            text='user.place_of_birth'
            :value="$user->personalInformation?->place_of_birth"
            :required="$user->isCollegist()"
            :disabled="user()->cannot('editStaticPersonalInformation', $user)"
            asterisk
            maxlength="225"
            />
        <x-input.datepicker
            l=6
            id='date_of_birth'
            required
            text='user.date_of_birth'
            :value="$user->personalInformation?->date_of_birth"
            :required="$user->isCollegist()"
            :disabled="user()->cannot('editStaticPersonalInformation', $user)"
            asterisk="true"
            :max="now()->subDay()->toDateString()"
            />
        <x-input.text
            id='mothers_name'
            required
            text='user.mothers_name'
            :value="$user->personalInformation?->mothers_name"
            :required="$user->isCollegist()"
            :disabled="user()->cannot('editStaticPersonalInformation', $user)"
            asterisk
            maxlength="225"
            />
        @endif
        <x-input.text
            id='phone_number'
            type='tel'
            autocomplete='tel'
            pattern="[+][0-9]{1,4}[-\s()0-9]*"
            minlength="8"
            maxlength="18"
            text='user.phone_number'
            helper='+36 (20) 123-4567'
            :value="$user->personalInformation?->phone_number"
            :required="$user->roles()->exists()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            />
        @if (!$user->isTenant() || $user->hasRoleOtherThanTenant() || isset($application))
        <x-input.select
            id="country"
            :elements="$countries"
            text="user.country"
            default="Hungary"
            :value="$user->personalInformation?->country"
            :required="$user->isCollegist()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            maxlength="255"
            />
        <x-input.text
            l=6 id='county'
            autocomplete='address-level1'
            text='user.county'
            required
            :value="$user->personalInformation?->county"
            :required="$user->isCollegist()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            maxlength="255"
            />
        <x-input.text
            l=6
            id='zip_code'
            autocomplete='postal-code'
            text='user.zip_code'
            :value="$user->personalInformation?->zip_code"
            :required="$user->isCollegist()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            maxlength="31"
            />
        <x-input.text
            id='city'
            autocomplete='address-level2'
            text='user.city'
            :value="$user->personalInformation?->city"
            :required="$user->isCollegist()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            maxlength="255"
        />
        <x-input.text
            id='street_and_number'
            autocomplete='street-address'
            text='user.street_and_number'
            :value="$user->personalInformation?->street_and_number"
            :required="$user->isCollegist()"
            asterisk
            :disabled="user()->cannot('edit', $user)"
            maxlength="255"
        />
        <x-input.text
            id='relatives_contact_data'
            text='user.relatives_contact_data'
            :helper="__('user.relatives_contact_data_desc')"
            :value="$user->personalInformation?->relatives_contact_data"
            :disabled="user()->cannot('edit', $user)"
            maxlength="255"
        />
        @endif
        @if ($user->isTenant() && !isset($application))
            <x-input.datepicker
                id='tenant_until'
                text='user.tenant_until'
                :helper="'('.__('user.in_case_of_tenants').')'"
                :value="$user->personalInformation?->tenant_until"
                required
                asterisk
                :min="\Carbon\Carbon::now()->format('Y-m-d')"
                :max="\Carbon\Carbon::now()->addMonths(6)->format('Y-m-d')"
                :disabled="user()->cannot('edit', $user)"
            />
        @endif

        @can('edit', $user)
        <x-input.button class="right" text="general.save"/>
        @endcan
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
