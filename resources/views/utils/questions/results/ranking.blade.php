
@php
   $rdata = $question->rankingData()
@endphp
@if(isset($rdata["results_named"]))
    @foreach($rdata["results_named"] as $rank => $name)
        <tr>
            <th>{{ $rank }}</th>
            <td>{{ $name }}</td>
        </tr>
    @endforeach
@endif
<tr>
    <td><details><summary>Computer-readable data of the voting (the order of the ballots is randomized):</summary>{{ json_encode($rdata) }}</details></td>
</tr>