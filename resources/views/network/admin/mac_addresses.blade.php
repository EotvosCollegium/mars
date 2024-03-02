@if (\App\Models\Feature::isFeatureEnabled("internet.wired"))
<span
    class="card-title">Regisztrált vezetékes eszközök kezelése
    @notification(\App\Models\Internet\MacAddress::class)
</span>
<blockquote class="error">
    A MAC címek jóváhagyása előtt ellenőrizd, hány címe van már a felhasználónak, és hogy nem routerről van-e szó
    (<a href="https://mac-address.alldatafeeds.com/mac-address-lookup" target="_blank">ellenőrizd pl. itt</a>). Szűrd a
    gyanús címeket (pl. 'ff:ff:ff:ff:ff'). Módosítsd a megjegyzést, ha szükséges.
</blockquote>
<div id="mac-addresses-table"></div>
<script type="text/javascript" src="{{ mix('js/moment.min.js') }}"></script>
<script type="application/javascript">
    $(document).ready(function () {
        const updateMacAddress = function (cell, data, payload) {
            $.ajax({
                type: "PUT",
                url: "{{ route('internet.mac_addresses.update', [':id']) }}".replace(':id', data.id),
                data: payload,
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
            onRendered(function () {
                $('.tooltipped').tooltip();
            });
            return $(`<button class="btn-floating green tooltipped" style="margin-right: 10px"  data-position="top" data-tooltip="Elfogad">
                            <i class="material-icons">check</i></button>
                        `)
                .click(function () {
                    updateMacAddress(cell, data, {'state': "{{\App\Models\Internet\MacAddress::APPROVED }}"});
                }).toggle(data.state !== '{{ \App\Models\Internet\MacAddress::APPROVED }}')
                .add($(`<button class="btn-floating tooltipped" style="margin-right: 10px"  data-position="top" data-tooltip="Letilt">
                            <i class="material-icons">block</i></button>
                        `)
                    .click(function () {
                        updateMacAddress(cell, data, {'state': "{{\App\Models\Internet\MacAddress::REJECTED }}"});
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

        var editCallback = function (cell) {
            const data = cell.getRow().getData();
            updateMacAddress(cell, data, {'comment': cell.getValue()});
        };

        var table = new Tabulator("#mac-addresses-table", {
            paginationSize: 5,
            pagination: "remote", //enable remote pagination
            ajaxURL: "{{ route('internet.mac_addresses.index') }}", //set url for ajax request
            ajaxSorting: true,
            ajaxFiltering: true,
            layout: "fitColumns",
            placeholder: "No Data Set",
            cellEdited: editCallback,
            columns: [
                {
                    title: "Felhasználó",
                    field: "user.name",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 150,
                },
                {
                    title: "MAC cím",
                    field: "mac_address",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 180,
                },
                {
                    title: "Megjegyzés",
                    field: "comment",
                    editor: "input",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 150
                },
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
                    minWidth: 130,
                },
                {
                    title: "Módosítás",
                    field: "state",
                    width: "130",
                    headerSort: false,
                    formatter: actions,
                    minWidth: 80
                },
                {
                    title: "Létrehozva",
                    field: "created_at",
                    sorter: "datetime",
                    formatter: dateFormatter,
                    headerFilter: 'input',
                    minWidth: 140,
                },
                {title: "Törlés", field: "id", width: "100", headerSort: false, formatter: deleteAction, minWidth: 20},
            ],
        });
    });
</script>
@endif
