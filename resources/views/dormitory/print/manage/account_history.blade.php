<span class="card-title">Előzmények</span>
<div id="account-history-table"></div>
<script type="application/javascript">
$(document).ready(function() {
    var table = new Tabulator("#account-history-table", {
        paginationSize: 20,
        layout: "fitColumns",
        pagination: "remote", //enable remote pagination
        ajaxURL: "{{ route('print-account-history.index') }}", //set url for ajax request
        ajaxSorting: true,
        ajaxFiltering: true,
        placeholder: "@lang('general.nothing_to_show')",
        headerSort: false,
        columns: [
            {
                title: "Felhasználó",
                field: "user.name",
                sorter: "string",
                headerFilter: 'input',
                minWidth:200
            },
            {
                title: "Egyenleg változás",
                field: "balance_change",
                sorter: "number",
                minWidth:100
            },
            {
                title: "Ingyenes oldal változás",
                field: "free_page_change",
                sorter: "number",
                minWidth:100
            },
            {
                title: "Határidő változás",
                field: "deadline_change",
                sorter: "date",
                minWidth:180
            },
            {
                title: "Módosító",
                field: "modifier.name",
                sorter: "string",
                headerFilter: 'input',
                minWidth:180
            },
            {
                title: "Módosítás ideje",
                field: "modified_at",
                sorter: "date",
                minWidth:180
            },
        ],
        initialSort: [
            {column: "modified_at", dir: "desc"}
        ]
    });
});
</script>
