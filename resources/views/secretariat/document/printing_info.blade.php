@if($printing_available)
    <blockquote>
        {{__("document.printing_first", [
            "printing_conf_name_hun" => $printing_conf_name_hun,
            "printing_conf_name_eng" => $printing_conf_name_eng,
            "current_balance" => $current_balance
        ])}}<a href="{{route('print.index')}}">{{__("document.printing_second")}}</a>{{__("document.printing_third")}}
    </blockquote>
@endif