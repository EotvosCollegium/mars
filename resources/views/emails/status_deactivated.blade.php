@component('mail::message')
    <h1>@lang('mail.dear') {{ $recipient }}!</h1>
    <p>
        A szemeszter végi értékelő formot nem töltötted ki a határidőn belül.
        Vedd fel a kapcsolatot a titkársággal, ellenkező estben a tagságod megszűnik és alumni státuszba kerülsz.
    </p>
@endcomponent
