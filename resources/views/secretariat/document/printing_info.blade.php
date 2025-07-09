@if($printing_available)
    <blockquote>
        @markdown(__("document.printing_first",
            [
                "printing_conf_name_hun" => $printing_conf_name_hun,
                "printing_conf_name_eng" => $printing_conf_name_eng,
                "current_balance" => $current_balance,
                "printing_page_url" => route('print.index')
            ]
        ))
    </blockquote>
@endif