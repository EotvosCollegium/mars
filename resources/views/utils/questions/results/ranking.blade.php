
@php
   $rdata = $question->rankingData()
@endphp

@if(isset($rdata["results_named"]))
<table>
    @foreach($rdata["results_named"] as $rank => $names)
        @foreach($names as $idx => $name)
            <tr>
                @if($idx == 0) <th rowspan="{{ count($names) }}">{{ $rank }}</th> @endif
                <td>{{ $name }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
@endif

@if(isset($rdata["stats"]))
    @include('utils.questions.results.ranking_stats',
        ['rdata' => $rdata, 'question' => $question]
    )
@endif

<table>
    <tr>
        <td><details>
            <summary>Computer-readable data of the election (the order of the ballots is randomized):</summary>
            <p>
                <button class="waves-effect waves-light btn" onclick="copyData('election-results-data')">
                    Copy data <i class="material-icons right">content_copy</i>
                </button>
            </p>
            <pre id="election-results-data">
                {{ json_encode($question->rankingData(), JSON_PRETTY_PRINT) }}
            </pre>
        </details></td>
    </tr>
</table>

@push('scripts')
<script>
    function copyData(id) {
        const data = document.getElementById(id).textContent;

        navigator.clipboard.writeText(data).then(() => {
            M.toast({html: '<span class="white-text">@lang("internet.copied")</span>'});
        }).catch(err => {
            M.toast({html: '<span class="white-text">Failed to copy data</span>'});
        });
    }
</script>
@endpush
