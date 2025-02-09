
@php
   $rdata = $question->rankingData()
@endphp
@if(isset($rdata["results_named"]))
    @foreach($rdata["results_named"] as $rank => $names)
        @foreach($names as $idx => $name)
            <tr>
                @if($idx == 0) <th rowspan="{{ count($names) }}">{{ $rank }}</th> @endif
                <td>{{ $name }}</td>
            </tr>
        @endforeach
    @endforeach
@endif
<tr>
    <td><details><summary>Computer-readable data of the voting (the order of the ballots is randomized):</summary>{{ json_encode($rdata) }}</details></td>
</tr>