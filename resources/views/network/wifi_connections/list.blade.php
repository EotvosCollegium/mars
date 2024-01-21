<span class="card-title">@lang('internet.wifi_connections')</span>
<blockquote>A táblázat éjjelente frissül.</blockquote>
<div id="wifi-connections-table"></div>
<script type="application/javascript">
    $(document).ready(function () {

        function leaseFormatter(cell, formatterParams, onRendered) {
            var start = cell.getRow().getData().lease_start;
            var end = cell.getRow().getData().lease_end;

            return start + " - " + end;
        }

        var table = new Tabulator("#wifi-connections-table", {
            paginationSize: 10,
            pagination: "remote",
            ajaxURL: "{{ route('internet.admin.wifi_connections.all') }}",
            ajaxSorting: true,
            ajaxFiltering: true,
            layout:"fitColumns",
            placeholder: "No Data Set",
            columns: [
                {
                    title: "@lang('internet.wifi_user')",
                    field: "wifi_username",
                    sorter: "string",
                    headerFilter: 'input'
                },
                {
                    title: "@lang('internet.mac_address')",
                    field: "mac_address",
                    sorter: "string",
                    headerFilter: 'input'
                },
                {
                    title: "IP cím",
                    field: "ip",
                    sorter: "string",
                    headerFilter: 'input'
                },
                {
                    title: "Megjegyzés",
                    field: "note",
                },
                {
                    title: "Lease",
                    field: "lease_start",
                    sorter: "string",
                    headerFilter: 'input',
                    formatter: leaseFormatter,
                },
                {
                    title: "Radius csatlakozások",
                    field: "radius_connections",
                },
            ],
        });
    });
</script>
