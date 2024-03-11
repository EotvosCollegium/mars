<div class="row course" id="course_{{$index}}" style="margin:0">
    <x-input.textarea id="courses[{{$index}}][name]"
                    l=5
                    text="Kurzus neve"
                    required >{{ $value ? $value['name'] ?? '' : '' }}</x-input.textarea>
    <x-input.textarea id="courses[{{$index}}][code]{{ $index }}"
                    l=3
                    text="Kurzus kódja"
                    required >{{ $value ? $value['code'] ?? '' : '' }}</x-input.textarea>
    <x-input.text id="courses[{{$index}}][grade]"
                    l=3 s=11
                    type="number"
                    min="1"
                    max="5"
                    text="Jegy"
                    :value="($value ? $value['grade'] ?? '' : '')"
                    helper="(ha ismert)" />
    <x-input.button type="button" s="1" class="right red" floating icon="delete" onclick="removeCourse({{$index}})"/>
</div>
