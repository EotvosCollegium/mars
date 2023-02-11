@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient }}!</h1>
<p>
    @lang('mail.new_registration', ['registered_user' => $user->name])
</p>
@php
    $personal=$user->personalInformation
@endphp
@component('mail::panel')
@lang('user.name'): {{$user->name}}<br>
@lang('user.email'): {{$user->email}}<br>
@lang('user.phone_number'): {{$personal->phone_number}}<br>
@lang('user.tenant_until'): {{$personal->tenant_until}}
@endcomponent
<div class="row">
@component('mail::button', ['url'=> route('secretariat.registrations.accept', ['id' => $user->id])])
@lang('user.accept')    
@endcomponent
</div>
<p>@lang('mail.administrators')</p>
@endcomponent
