@component('mail::message')
<h1>@lang('mail.dear') {{ $recipient->name }}!</h1>
<style>
    table, tr, th, td { background: #FFF; border-collapse: collapse; vertical-align: top; }
    table { background: #FFF; border: 1px solid #E0E0E0; }
    table th, table td { border: solid #E0E0E0; border-width: 1px 0; padding: 8px 10px; }
    table th { font-weight: bold; text-align: left; }
</style>
<table>
    <thead>
        <tr>
            <th>
                <p>Egy hiba történt a következő részletekkel:</p>
                <p style="font-size: 14px; font-weight: normal; margin: .5em 0 0;">Üzenet:<br> {{ $exception->getMessage() ?? '' }}</p>
                <p style="font-size: 14px; font-weight: normal; margin: .5em 0 0;">URL: {{ $url ?? '' }}</p>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <span style="display: block; margin-top: 3px; font-size: 13px; color:#242851">in
                    <span title="{{ $exception->getFile() ?? '' }}">
                        <strong>{{ $exception->getFile() ?? '' }}</strong>
                        line {{ $exception->getLine() ?? '' }}
                    </span>
                </span>
            </td>
        </tr>
        @foreach(($exception->getTrace() ?? []) as $value)
        <tr>
            <td>
                at <span style="font-size: 15px; font-weight: bold; line-height: 1.3; margin: 0; position: relative;">
                    <span style="color: #b38f2f">
                        {{ basename($value['class'] ?? '!no class given!') }}
                    </span>
                </span>
                <span style="padding: 0 2px">→</span>
                <span style="font-weight: bold;font-family: Consolas;color:#b38f2f">{{ $value['function'] ?? '' }}()</span>
                <span style="display: block; margin-top: 3px; font-size: 13px;" >in
                    <span>
                        <strong style="color:#242851">{{ $value['file'] ?? '!no file given!' }}</strong>
                        line {{ $value['line'] ?? '!no line given!' }}
                    </span>
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endcomponent
