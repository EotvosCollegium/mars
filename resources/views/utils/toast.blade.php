@push('scripts')
    {{-- basic toast notification --}}
    @if (session('message'))
        <script>
            M.toast({html: "{{ session('message') }}"});
        </script>
    @endif

    {{-- for general errors only that cannot be linked to particular inputs --}}
    @if (session('error'))
        <script>
            var toastHTML = `
            <i class='material-icons' style='margin-right:5px'>error</i>
            {{ session('error') }}
            <button
                class='btn-flat toast-action'
                onclick="dismissToast()">
                <i class='material-icons '>clear</i>
            </button>
            `;

            function dismissToast() {
                M.Toast.dismissAll();
            }

            M.toast({
                html: toastHTML,
                displayLength: 10000,
            });
        </script>
    @endif

    <script>
        function ajaxError(message) {
            const warningIcon = `<i class='material-icons' style='margin-right:5px'>error</i> `;
            const text = "responseJSON" in message
                ? message.responseJSON.message
                : "@lang('general.server_error')";
            M.toast({
                html: warningIcon + text,
            });
            console.error(message);
        }
    </script>

    @if($errors->any())
        <script>
            var toastHTML = `
            <i class='material-icons' style='margin-right:5px'>error</i>
            @lang('general.validation_errors')
            `;
            M.toast({
                html: toastHTML,
            });
            console.warn(`{{var_dump($errors->all())}}`)
        </script>
    @endif
@endpush
