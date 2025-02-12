{{--
Draws the table containing vote counts at each round of elimination
for STV votes.
--}}

@php
$stats = $rdata['stats'];
$winners = array_slice($rdata['results'], 0, $question->max_options);
$optionIds = array();
$roundCount = count($stats['rounds'])
@endphp

<p style="font-size: 20px">
    @lang('general.details')
</p>

<p>
    @lang('voting.votes_needed_to_win'): {{ $stats['Votes Needed to Win'] }}
</p>

<tr><td colspan="2">
    <table class="ranking-stats">
        <thead>
            <tr>
            @foreach($stats['rounds'][1] as $optionId => $voteNumber)
                <th @class(['winner' => in_array($optionId, $winners)])>
                    {{$question->options()->where('id', $optionId)->first()->initials()}}
                </th>
                @php $optionIds[] = $optionId; @endphp
            @endforeach
            </tr>
        </thead>
        <tbody>
            @for($i=1; $i <= $roundCount; ++$i)
            <tr>
                @foreach($optionIds as $optionId)
                @php
                    $value = $stats['rounds'][$i][$optionId] ?? "–";
                    if ("–" != $value) $value = round($value, 2);
                    $isWinner = in_array($optionId, $winners);
                    $isColored = "–" == $value || $roundCount == $i || !isset($stats['rounds'][$i+1][$optionId]);
                @endphp
                <td @class([
                    'winner' => $isWinner,
                    'colored' => $isColored,
                    'round-of-elimination' => $isColored && "–" != $value
                ])>
                    {{$value}}
                </td>
                @endforeach
            </tr>
            @endfor
        </tbody>
    <table>
</tr>