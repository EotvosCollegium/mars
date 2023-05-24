<div class="row course" id="course_{{$index}}" style="margin:0">
    <input type="hidden" name="course_indices[]" value="{{$index}}">
    <x-input.text id="course_name_{{ $index }}"
                    l=5
                    text="Kurzus neve"
                    :value="$value?->name"
                    required />
    <x-input.text id="course_code_{{ $index }}"
                    l=3
                    text="Kurzus kódja"
                    :value="$value?->code"
                    required />
    <x-input.text id="course_grade_{{ $index }}"
                    l=3 s=11
                    type="number"
                    min="1"
                    max="5"
                    text="Jegy"
                    :value="$value?->code"
                    helper="(ha ismert)" />
    <x-input.button type="button" s="1" class="right red" floating icon="delete" onclick="removeCourse({{$index}})"/>
</div>
