<div id="mac-addresses-table"></div>
<script type="text/javascript" src="{{ mix('js/moment.min.js') }}"></script>
<script type="application/javascript">
    $(document).ready(function () {
        var actions = function (cell, formatterParams, onRendered) {
            var data = cell.getRow().getData();
            var changeState = function (state) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('internet.mac_addresses.edit', [':id']) }}".replace(':id', data.id),
                    data: {
                        'state': state
                    },
                    success: function (response) {
                        response = {...data, ...response};
                        cell.getTable().updateData([response]);
                        cell.getRow().reformat();
                    },
                    error: function (error) {
                        ajaxError('Hiba', 'Ajax hiba', 'ok', error);
                    }
                });
            };

            return $("<button type=\"button\" style=\"margin: 2px;\" class=\"btn waves-effect red\">Elutasít</button></br>")
                .click(function () {
                changeState('rejected');
            }).toggle(data._state === '{{ \App\Models\Internet\MacAddress::REQUESTED }}')
                .add($("<button type=\"button\" style=\"margin: 2px;\" class=\"btn waves-effect green\">Elfogad</button></br>")
                    .click(function () {
                changeState('approved');
            }).toggle(data._state === '{{ \App\Models\Internet\MacAddress::REQUESTED }}'))
                .add($("<button type=\"button\" style=\"margin: 2px;\" class=\"btn waves-effect\">Visszavon</button></br>")
                    .click(function () {
                changeState('requested');
            }).toggle(data._state !== '{{ \App\Models\Internet\MacAddress::REQUESTED }}')).wrapAll('<div></div>').parent()[0];
        };

        var deleteButton = function (cell, formatterParams, onRendered) {
            return $("<button type=\"button\" class=\"btn waves-effect btn-fixed-height coli blue\">Törlés</button>").click(function () {
                var data = cell.getRow().getData();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('internet.mac_addresses.delete', [':id']) }}".replace(':id', data.id),
                        success: function () {
                            cell.getTable().setPage(cell.getTable().getPage());
                        },
                        error: function (error) {
                            ajaxError('Hiba', 'Ajax hiba', 'ok', error);
                        }
                    });
            })[0];
        };
        var dateFormatter = function(cell, formatterParams){
            var value = cell.getValue();
            if(value){
                value = moment(value).format("YYYY. MM. DD. HH:mm");
            }
            return value;
        }

        var table = new Tabulator("#mac-addresses-table", {
            paginationSize: 10,
            pagination: "remote", //enable remote pagination
            ajaxURL: "{{ route('internet.admin.mac_addresses.all') }}", //set url for ajax request
            ajaxSorting: true,
            ajaxFiltering: true,
            layout:"fitColumns",
            placeholder: "No Data Set",
            columns: [
                {
                    title: "Felhasználó",
                    field: "user.name",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth:200,
                },
                {
                    title: "MAC cím",
                    field: "mac_address",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth:180,
                },
                {title: "Megjegyzés", field: "comment", sorter: "string", headerFilter: 'input', minWidth:150},
                {
                    title: "Létrehozva",
                    field: "created_at",
                    sorter: "datetime",
                    formatter: dateFormatter,
                    headerFilter: 'input',
                    minWidth:170,
                },
                {
                    title: "Státusz", field: "state", sorter: "string", headerFilter: 'select',
                    headerFilterParams: {
                        "rejected": "Elutasított",
                        "approved": "Jóváhagyott",
                        "requested": "Elbírálásra vár"
                    },
                    minWidth:140,
                },
                {title: "", field: "state", width:"130", headerSort: false, formatter: actions, minWidth:140},
                {title: "", field: "id", headerSort: false, formatter: deleteButton, minWidth:140},
            ],
        });
    });
</script>
