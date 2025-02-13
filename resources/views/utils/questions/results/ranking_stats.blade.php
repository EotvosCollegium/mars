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

@php
$numberOfBallots = count($rdata["ballots"]);
$numberOfAbstentions = count(array_filter(
    $rdata["ballots"],
    function ($ballot) { return 0 == count($ballot); }
));
@endphp

<blockquote>
    <ul>
        <li><strong>@lang('voting.number_of_ballots'):</strong> {{$numberOfBallots}}</li>
        <li>@lang('voting.number_of_abstentions'): {{$numberOfAbstentions}}</li>
        <li>@lang('voting.number_of_valid_ballots'): {{$numberOfBallots - $numberOfAbstentions}}</li>
        <li>@lang('voting.number_of_seats'): {{$question->max_options}}</li>
        <li><strong>@lang('voting.votes_needed_to_win'):</strong> {{ $stats['Votes Needed to Win'] }}</li>
    </ul>
</blockquote>

<tr><td colspan="2">
    <table class="ranking-stats">
        <thead>
            <tr>
            @foreach($stats['rounds'][1] as $optionId => $voteNumber)
                <th @class(['winner' => in_array($optionId, $winners)])>
                    <span title='{{ $question->options()->where('id', $optionId)->first()->title }}'>{{$question->options()->where('id', $optionId)->first()->initials()}}</span>
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