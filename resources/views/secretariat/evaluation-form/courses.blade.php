<form method="POST" action="{{ route('secretariat.evaluation.store') }}">
    @csrf
    <blockquote>Alfonsó és nyelvi szintfelmérő kurzusokkal együtt.</blockquote>
    <input type="hidden" name="section" value="courses"/>
    @foreach($evaluation?->courses ?? [] as $course)
        @include('secretariat.evaluation-form.course', ['index' => $loop->index, 'value' => $course])
    @endforeach
    <x-input.button type="button" id="addCourse" floating icon="add" onclick="insertEmptyCourse()" />

    <blockquote>
        <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">CTSZK 7. § (4) a.</a>
        A collegiumi tagság automatikusan megszűnik, ha a hallgató egy aktív félévben egyetlen collegiumi órát sem vett fel.<br>
        <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">CTSZK 7. § (5) b.</a>
        A Collegiumból elbocsátható, aki a Collegiumban felvett óráját nem teljesítette.
    </blockquote>
    <div class="row">
        <x-input.text l=10 id="courses_note" :value="$evaluation?->courses_note" text="Megjegyzés, helyesbítés"/>
        <x-input.button l=2 class="right" text="general.save" />
    </div>
</form>

@push('scripts')
<script>
function removeCourse(index) {
    $("#course_" + index).remove();
}
let courseCounter = {{count($evaluation?->courses ?? []) ?? 0}}
$(document).ready(function(){
    if(courseCounter == 0) {
        insertEmptyCourse();
    }
  });
function insertEmptyCourse() {
    let index = courseCounter++;
    let text = `
    @include('secretariat.evaluation-form.course', ['index' => '.index.', 'value' => null])
    `
    $(text.replace(/.index./g, index)).insertBefore('#addCourse');
    $('select').formSelect();
}
</script>
@endpush
