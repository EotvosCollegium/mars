@foreach($question->options->sortByDesc('votes') as $option)
<tr>
    <td>{{$option->title}}</td>
    @if($question->hasBeenOpened())
    <td><b>{{$option->votes}}</b></td>
    @endif
</tr>
@endforeach