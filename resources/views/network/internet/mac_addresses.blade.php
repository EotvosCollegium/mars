<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('internet.your_registered_devices')</span>
        <blockquote>
            <p>@lang('internet.registered_devices_info')</p>
        </blockquote>
        <form action="{{ route('internet.mac_addresses.store') }}" method="post">
            <div class="form-row align-items-center">
                @csrf
                <div class="row">
                    <x-input.text l=5 id="mac_address" placeholder="01:23:45:67:89:AB" required
                                  text="internet.mac_address"/>
                    <x-input.text l=5 id="comment" :placeholder="__('internet.mac_comment')" required
                                  text="general.comment"/>
                    <x-input.button l=2 text="general.add"/>
                </div>
            </div>
        </form>
        <div id="mac-addresses-table"></div>
        <script type="text/javascript">
            $(document).ready(function () {
                var deleteButton = function (cell, formatterParams, onRendered) {
                    return $("<button type=\"button\" class=\"btn waves-effect btn-fixed-height coli blue right\">@lang('internet.delete')</button>").click(function () {
                        var data = cell.getRow().getData();
                        $.ajax({
                            type: "DELETE",
                            url: "{{ route('internet.mac_addresses.destroy', [':id']) }}".replace(':id', data.id),
                            success: function () {
                                cell.getRow().delete();
                                M.toast({html: "{{__('general.successfully_deleted')}}"});
                            },
                            error: function (error) {
                                ajaxError(error);
                            }
                        });
                    })[0];
                };
                var table = new Tabulator("#mac-addresses-table", {
                    paginationSize: 10,
                    layout: "fitColumns",
                    data: {!! $internet_access->macAddresses !!},
                    placeholder: "@lang('internet.nothing_to_show')",
                    columnMinWidth: 150,
                    headerSort: false,
                    columns: [
                        {title: "@lang('internet.mac_address')", field: "mac_address", sorter: "string"},
                        {title: "@lang('general.comment')", field: "comment", sorter: "string"},
                        {title: "@lang('internet.state')", field: "translated_state", sorter: "string"},
                        {title: "", field: "id", headerSort: false, formatter: deleteButton},
                    ]
                });
            });
        </script>
    </div>
</div>
