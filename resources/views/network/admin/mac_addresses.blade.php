<span
    class="card-title">Regisztrált vezetékes eszközök kezelése
    @notification(\App\Models\Internet\MacAddress::class)
</span>
<div id="mac-addresses-table"></div>
<script type="text/javascript" src="{{ mix('js/moment.min.js') }}"></script>
<script type="application/javascript">
    $(document).ready(function () {
        const updateMacAddress = function (cell, data, state) {
            $.ajax({
                type: "PUT",
                url: "{{ route('internet.mac_addresses.update', [':id']) }}".replace(':id', data.id),
                data: {
                    'state': state
                },
                success: function (response) {
                    response = {...data, ...response};
                    cell.getTable().updateData([response]);
                    cell.getRow().reformat();
                    M.toast({html: "{{ __('general.successful_modification') }}"});
                },
                error: function (error) {
                    ajaxError(error);
                }
            });
        };
        const deleteMacAddress = function (cell, data) {
            $.ajax({
                type: "DELETE",
                url: "{{ route('internet.mac_addresses.destroy', [':id']) }}".replace(':id', data.id),
                success: function (response) {
                    cell.getRow().delete();
                    M.toast({html: "{{ __('general.successfully_deleted') }}"});
                },
                error: function (error) {
                    ajaxError(error);
                }
            });
        };
        var actions = function (cell, formatterParams, onRendered) {
            const data = cell.getRow().getData();

            return $(`<button class="btn-floating green" style="margin-right: 10px">
                            <i class="material-icons">check</i></button>
                        `)
                .click(function () {
                    updateMacAddress(cell, data, "{{\App\Models\Internet\MacAddress::APPROVED }}");
                }).toggle(data.state !== '{{ \App\Models\Internet\MacAddress::APPROVED }}')
                .add($(`<button class="btn-floating" style="margin-right: 10px">
                            <i class="material-icons">block</i></button>
                        `)
                    .click(function () {
                        updateMacAddress(cell, data, "{{\App\Models\Internet\MacAddress::REJECTED }}");
                    }).toggle(data.state !== '{{ \App\Models\Internet\MacAddress::REJECTED }}'))
                .wrapAll('<div></div>').parent()[0];

        };

        var deleteAction = function (cell, formatterParams, onRendered) {
            return $(`<button class="btn-floating red" style="margin-right: 10px">
                            <i class="material-icons">delete_forever</i></button>
                        `)
                .click(function () {
                    deleteMacAddress(cell, cell.getRow().getData());
                })[0];
        };

        var dateFormatter = function (cell, formatterParams) {
            let value = cell.getValue();
            if (value) {
                value = moment(value).format("YYYY. MM. DD. HH:mm");
            }
            return value;
        }

        var table = new Tabulator("#mac-addresses-table", {
            paginationSize: 5,
            pagination: "remote", //enable remote pagination
            ajaxURL: "{{ route('internet.mac_addresses.index') }}", //set url for ajax request
            ajaxSorting: true,
            ajaxFiltering: true,
            layout: "fitColumns",
            placeholder: "No Data Set",
            initialFilter: [
                {field: "state", value: "requested"}
            ],
            columns: [
                {
                    title: "Felhasználó",
                    field: "user.name",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 200,
                },
                {
                    title: "MAC cím",
                    field: "mac_address",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 180,
                },
                {title: "Megjegyzés", field: "comment", sorter: "string", headerFilter: 'input', minWidth: 150},

                {
                    title: "Státusz",
                    field: "state",
                    sorter: "string",
                    headerFilter: 'select',
                    headerFilterParams: {
                        "rejected": "Elutasított",
                        "approved": "Jóváhagyott",
                        "requested": "Elbírálásra vár"
                    },
                    minWidth: 140,
                },
                {
                    title: "Létrehozva",
                    field: "created_at",
                    sorter: "datetime",
                    formatter: dateFormatter,
                    headerFilter: 'input',
                    minWidth: 170,
                },
                {title: "", field: "state", width: "130", headerSort: false, formatter: actions, minWidth: 140},
                {title: "", field: "id", width: "130", headerSort: false, formatter: deleteAction, minWidth: 140},
            ],
        });
    });
</script>
