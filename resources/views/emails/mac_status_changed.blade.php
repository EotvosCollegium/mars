@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        @lang('internet.mac_status_changed_desc', ['mac' => $mac, 'status' => $status])
    </p>
@endcomponent
