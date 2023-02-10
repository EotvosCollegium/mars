@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        {{ $reporter}} @lang('mail.report_internet_fault', ['os' => $user_os])
    </p>
    <p>
        {{ $report }}
    </p>
    <p>System</p>
@endcomponent
