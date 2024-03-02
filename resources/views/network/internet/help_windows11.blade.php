@extends('layouts.app')
@section('title')
    <i class="material-icons left">wifi</i>Windows 11 wireless setup
@endsection


@section('content')
<div class="card">
    <div class="card-content">
        <span class="card-title">Windows 11 wireless setup</span>
        <style>
            .simple_image {
                width: 600px;
            }
        </style>
        <ol>
            <li>
                Search for "Control Panel" and open it
                <br>
                <img class="simple_image" src="/internet_instructions/control_panel.png">
            </li>
            <li>
                Select "Network and Internet"
                <br>
                <img class="simple_image" src="/internet_instructions/network_and_internet.png">
            </li>
            <li>
                Select "Network and Sharing Centre" (Hálózati és megosztási központ)
                <br>
                <img class="simple_image" src="/internet_instructions/network_and_sharing_centre.png">
            </li>
            <li>
                Open "Set up a new connection or network"
                <br>
                <img class="simple_image" src="/internet_instructions/set_up_a_new_connection_or_network.png">
            </li>
            <li>
                Double-click on "Manually connect to a wireless network"
                <br>
                <img class="simple_image" src="/internet_instructions/manually_connect.png">
            </li>
            <li>
                Enter the wifi network’s name (<code>{{ config("internet.wifi_ssid") }}</code>) and set the Security type to "WPA2-Enterprise"
                <br>
                <img class="simple_image" src="/internet_instructions/ejcwifi_radius.png">
            </li>
            <li>
                Click on "Change connection settings"
                <br>
                <img class="simple_image" src="/internet_instructions/change_connection_settings.png">
            </li>
            <li>
                Click on "Security"
                <br>
                <img class="simple_image" src="/internet_instructions/security.png">
            </li>
            <li>
                Click on "Settings" near to "Microsoft: Protected EAP (PEAP)"
                <br>
                <img class="simple_image" src="/internet_instructions/settings.png">
            </li>
            <li>
                Select "Configure...":
                <br>
                <img class="simple_image" src="/internet_instructions/configure.png">
            </li>
            <li>
                If checked, uncheck "Automatically use my Windows log-on name and password (and domain if any)" and then
                click "OK":
                <br>
                <img class="simple_image" src="/internet_instructions/automatically_use_windows.png">
            </li>
            <li>
                You have two options:
                <ol>
                    <li>
                        Validate the servers identity:<br>
                        If you want to validate it, select "Connect to these servers", and enter <code>{{ config("internet.wifi_domain") }}</code> to the
                        appropriate field, and then click "Ok".
                        <br>
                        <img class="simple_image" src="/internet_instructions/verify_identity.png">
                    </li>
                    <li>
                        Do not validate the servers identity<br>
                        If you don’t want to validate the server’s identity, click deselect "Verify the server’s identity by
                        validating the certificate" and then click "OK":
                        <br>
                        <img class="simple_image" src="/internet_instructions/dont_verify.png">
                    </li>
                </ol>
            </li>
            <li>

                Click "Advanced Settings":
                <br>
                <img class="simple_image" src="/internet_instructions/advanced_settings.png">
            </li>
            <li>

                Check "Specify authentication mode", and select "User authentication", and click "OK".
                <br>
                <img class="simple_image" src="/internet_instructions/authentication_mode.png">
            </li>
            <li>

                Click "Ok":
                <br>
                <img class="simple_image" src="/internet_instructions/ok.png">
            </li>
            <li>

                Click "Close":
                <br>
                <img class="simple_image" src="/internet_instructions/close.png">
            </li>
            <li>

                Close Control Panel:
                <br>
                <img class="simple_image" src="/internet_instructions/x.png">
            </li>
            <li>
                Once you are in the range of the WiFi network, open up where you can connect to WiFi networks, and select
                <code>{{ config("internet.wifi_ssid") }}</code>, connect to it, you will be prompted to enter your username
                (<code>{{ $internet_access->wifi_username }}</code>) and password.
                @if ($internet_access->wifi_password != null)
                    Your password:
                    <span id="pw" style="cursor: pointer; font-family: Monospace;">
                        <button class="waves-effect btn-small">@lang('internet.show')</button></span>
                @else
                    You need to create a password in the <a href="{{ route('internet.index') }}">internet page</a>.
                @endif
        </ol>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            document.getElementById("pw").addEventListener("click", function() {
                $(this).text('{{ $internet_access->wifi_password }}');
                var copyText = document.getElementById("pw");
                var textArea = document.createElement("textarea");
                textArea.value = copyText.textContent;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand("Copy");
                textArea.remove();
                M.toast({
                    html: '<span class="white-text">@lang('internet.copied')</span>'
                });
            });
        });
    </script>
@endpush
