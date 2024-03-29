<script type="text/javascript" src="{{ mix('js/moment.min.js') }}"></script>
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('print.history')</span>
        <div id="print-history-table"></div>
        <script type="application/javascript">
        $(document).ready(function() {
            var deleteButton = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();
                if(data.state == '@lang('print.'.App\Models\PrintJob::QUEUED)'){
                    return $(
                    "<button type=\"button\" class=\"btn waves-effect btn-fixed-height  coli blue\">@lang('print.cancel_job')</button>"
                    ).click(function() {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('print.print_jobs.cancel', [':id']) }}".replace(':id', data.id),
                            success: function() {
                                cell.getTable().setPage(cell.getTable().getPage());
                            },
                            error: function(error) {
                                ajaxError(
                                    '@lang('internet.error')',
                                    '@lang('internet.ajax_error')',
                                    'ok',
                                    error
                                );
                            }
                        });
                    })[0];
                } else {
                    return '';
                }

            };
            var dateFormatter = function(cell, formatterParams){
                var value = cell.getValue();
                if(value){
                    value = moment(value).format("YYYY. MM. DD. HH:mm");
                }
                return value;
            }
            var table = new Tabulator("#print-history-table", {
                paginationSize: 10,
                layout: "fitColumns",
                pagination: "remote", //enable remote pagination
                ajaxURL: "{{ $route }}", //set url for ajax request
                ajaxSorting: true,
                ajaxFiltering: true,
                placeholder: "@lang('internet.nothing_to_show')",
                headerSort: false,
                columnMinWidth:200,
                columns: [{
                        title: "@lang('internet.created_at')",
                        field: "created_at",
                        sorter: "datetime",
                        formatter:dateFormatter,
                        @can('viewAny', App\Models\PrintJob::class) headerFilter: 'input' @endcan
                    },
                    @can('viewAny', App\Models\PrintJob::class)
                    {
                        //TODO empty when not in admin view
                        title: "@lang('print.user')",
                        field: "user.name",
                        sorter: "string",
                        headerFilter: 'input'
                    },
                    @endcan
                    {
                        title: "@lang('print.document')",
                        field: "filename",
                        sorter: "string",
                        @can('viewAny', App\Models\PrintJob::class) headerFilter: 'input' @endcan
                    },
                    {
                        title: "@lang('print.cost')",
                        field: "cost",
                        sorter: "string",
                        @can('viewAny', App\Models\PrintJob::class)  headerFilter: 'input' @endcan
                    },
                    {
                        title: "@lang('print.state')",
                        field: "state",
                        sorter: "string",
                        @can('viewAny', App\Models\PrintJob::class)
                        headerFilterParams: {
                            @foreach(\App\Models\PrintJob::STATES as $key => $state)
                            "{{ $state }}": "@lang('print.' . $state)",
                            @endforeach
                        }
                        @endcan
                    },
                    {title: "", field: "id", headerSort: false, formatter: deleteButton},
                ],
            });
        });
        </script>

    </div>
</div>
