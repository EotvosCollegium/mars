@foreach($question->longAnswers as $answer)
<tr>
    <td>{{ $answer->text }}</td>
</tr>
@endforeach