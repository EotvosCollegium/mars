<span class="card-title">@lang('internet.wifi_connections')</span>
<blockquote>Itt látható, hogy egy felhasználó egy adott MAC címmel milyen IP címet kapott milyen időtartamban (lease),
    és azzal hányszor csatlakozott fel a hálózatra. A táblázat éjjelente frissül.
</blockquote>
<div id="wifi-connections-table"></div>
<script type="application/javascript">
    $(document).ready(function () {

        function leaseFormatter(cell, formatterParams, onRendered) {
            var start = cell.getRow().getData().lease_start;
            var end = cell.getRow().getData().lease_end;

            return start + " - " + end;
        }

        var table = new Tabulator("#wifi-connections-table", {
            paginationSize: 20,
            pagination: "remote",
            ajaxURL: "{{ route('internet.wifi_connections.index') }}",
            ajaxSorting: true,
            ajaxFiltering: true,
            layout: "fitColumns",
            placeholder: "No Data Set",
            tableBuilt: function() {
                // Add "input-field" class to header filter divs
                $('.tabulator-header-filter').addClass('input-field');
            },
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
