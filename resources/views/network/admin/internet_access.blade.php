<span class="card-title">Internetelérés</span>
<blockquote>
    <p>Az aktuális internetelérés határidő: <span class="coli-text text-orange">{{ $activation_date }}</span></p>
    <p>Collegisták esetén a Netreg befizetésekor automatikusan beállítja a rendszer a hozzáférésüket eddig a
        dátumig.</p>
    <p>Vendégek esetén belépéskor meg kell adniuk a kiköltözés dátumát, amihez szinkronizáljuk az internet
        hozzáférésüket. Ha egy volt colis kér netet, csak adj neki vendég jogosultságot, és első belépéskor be tudja
        állítani már a netet magának.</p>
</blockquote>

<blockquote class="error">Az internet hozzáférést manuálisan csak kivételes esetekben hosszabbítsd!</blockquote>

<div id="net_accesses-table"></div>
<script type="text/javascript" src="{{ mix('js/moment.min.js') }}"></script>
<script type="application/javascript">
    $(document).ready(function () {
        var activation_date = new Date("{{ $activation_date }}");
        var now = new Date();
        var actions = function (cell, formatterParams, onRendered) {
            var data = cell.getRow().getData();
            var active = (new Date(data.has_internet_until));
            return $("<button class=\"btn waves-effect\" title=\"{{ $activation_date }}\">Frissítés</button>")
                .click(function () {
                    saveData(cell, {...data, has_internet_until: "{{ $activation_date }}"});
                }).toggle(data.has_internet_until == null || activation_date > active)
                .add($("<button class=\"btn waves-effect\">Deaktivál</button>")
                    .click(function () {
                        saveData(cell, {...data, has_internet_until: null});
                    }).toggle(data.has_internet_until != null && active >= now)).wrapAll('<div></div>').parent()[0];
        };

        var saveData = function (cell, data = null) {
            $(cell.getRow().getElement()).addClass('tabulator-unsaved');
            if (data == null) data = cell.getRow().getData();
            $.ajax({
                type: "POST",
                url: "{{ route('internet.internet_accesses.extend', [':id']) }}".replace(':id', data.user_id),
                data,
                success: function (response) {
                    response = {...data, ...response, id: response.user_id};
                    cell.getTable().updateData([response]);
                    cell.getRow().reformat();
                    $(cell.getRow().getElement()).removeClass('tabulator-unsaved');
                },
                error: function (error) {
                    ajaxError('Hiba', 'AJAX hiba', 'OK', error);
                }
            });
        };

        var editCallback = function (cell) {
            saveData(cell);
        };

        var dateFormatter = function (cell, formatterParams) {
            var value = cell.getValue();
            if (value) {
                value = moment(value).format("YYYY. MM. DD. HH:mm");
            }
            return value;
        }

        var table = new Tabulator("#net_accesses-table", {
            paginationSize: 5,
            pagination: "remote", //enable remote pagination
            ajaxURL: "{{ route('internet.internet_accesses.index') }}", //set url for ajax request
            ajaxSorting: true,
            ajaxFiltering: true,
            layout: "fitColumns",
            placeholder: "No Data Set",
            cellEditing: function (cell) {
                $(cell.getRow().getElement()).addClass('tabulator-unsaved');
            },
            cellEditCancelled: function (cell) {
                $(cell.getRow().getElement()).removeClass('tabulator-unsaved');
            },
            cellEdited: editCallback,
            columns: [
                {
                    title: "Felhasználó",
                    field: "user.name",
                    sorter: "string",
                    headerFilter: 'input',
                    minWidth: 200,
                },
                {
                    title: "Internetelérés",
                    field: "has_internet_until",
                    sorter: "datetime",
                    formatter: dateFormatter,
                    editor: 'dateEditor',
                    minWidth: 200,
                },
                {
                    title: "Alap MAC slot",
                    field: "auto_approved_mac_slots",
                    sorter: "number",
                    editor: 'number',
                    validator: "min:0",
                    minWidth: 50
                },
                {title: "", field: "state", headerSort: false, formatter: actions, minWidth: 150},
            ],
            ajaxResponse: function (url, params, response) {
                response.data = response.data.map(record => {
                    return {...record, id: record.user_id}
                });
                return response;
            },
        });
    });
</script>


