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
                return $(`<button class="btn-floating" style="margin-right: 10px">
                            <i class="material-icons">update</i></button>
                        `)
                    .click(function () {
                        sendExtendRequest(cell, data.user_id, "{{ $activation_date }}");
                    }).toggle(data.has_internet_until == null || active < activation_date)
                    .add($(`<button class="btn-floating red" style="margin-right: 10px">
                            <i class="material-icons">block</i></button>
                        `)
                        .click(function () {
                            sendRevokeRequest(cell, data.user_id);
                        }).toggle(data.has_internet_until != null && active >= now)).wrapAll('<div></div>').parent()[0];
            };

            var dateFormatter = function (cell, formatterParams, onRendered) {
                onRendered(function () {
                    $('.datepicker').datepicker({
                        format: 'yyyy. mm. dd.',
                        firstDay: 1,
                        yearRange: 50,
                        minDate: new Date(),
                        container: 'body',
                    });
                });
                let value = cell.getValue();
                if (value) {
                    value = moment(value).format("YYYY. MM. DD. HH:mm");
                } else {
                    value = 'Nincs hozzáférés'
                }
                const data = cell.getRow().getData();
                return $("<input type=\"text\" class=\"datepicker\" value=\"" + value + "\"/>")
                    .on('change', function () {
                        if (this.value) {
                            const value = moment(this.value + " 23:59", "YYYY. MM. DD. HH:mm").format("YYYY-MM-DD HH:mm");
                            sendExtendRequest(cell, data.user_id, value);
                        }

                    }).wrapAll('<div></div>').parent()[0];

            }

            var sendExtendRequest = function (cell, id, date) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('internet.internet_accesses.extend', [':id']) }}".replace(':id', id),
                    data: {
                        has_internet_until: date,
                    },
                    success: function (response) {
                        M.toast({html: "{{ __('general.successful_modification') }}"});
                        cell.getRow().getCell('has_internet_until').setValue(response, true)
                        cell.setValue(response, true);

                    },
                    error: ajaxError
                });
            };

            var sendRevokeRequest = function (cell, id, date) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('internet.internet_accesses.revoke', [':id']) }}".replace(':id', id),
                    success: function (response) {
                        M.toast({html: "{{ __('general.successful_modification') }}"});
                        console.log(response);
                        cell.getRow().getCell('has_internet_until').setValue(null, true);
                        cell.setValue(response, true);
                    },
                    error: ajaxError
                });
            };


            var table = new Tabulator("#net_accesses-table", {
                paginationSize: 5,
                pagination: "remote", //enable remote pagination
                ajaxURL: "{{ route('internet.internet_accesses.index') }}", //set url for ajax request
                ajaxSorting: true,
                ajaxFiltering: true,
                layout: "fitColumns",
                placeholder: "No Data Set",
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
                        minWidth: 50,
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
        }
    )
    ;
</script>


