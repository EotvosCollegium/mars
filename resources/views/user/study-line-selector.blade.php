<div class="row study_line" id="study_line_{{$index}}" style="margin:0">
    <input type="hidden" name="study_line_indices[]" value="{{$index}}">
    <x-input.text id="study_line_name_{{ $index }}"
                    xl=4
                    text="user.study_line"
                    :value="$value?->name"
                    required />
    <x-input.select id="study_line_level_{{ $index }}"
                    xl=3
                    text="user.study_line_level"
                    :value="$value?->type"
                    :elements="App\View\Components\Input\Select::convertArray([
                        'bachelor' => 'BA/BSc',
                        'master' => 'MA/Msc',
                        'phd' => 'Phd',
                        'ot' => 'Osztatlan',
                        'other' => ' Egyéb',
                    ])"
                    required />
    <x-input.select id="study_line_start_{{ $index }}"
                xl=2 s=6
                text="user.study_line_start"
                :value="$value?->start"
                :elements="\App\Models\Semester::all()"
                required />
    <x-input.select id="study_line_end_{{ $index }}"
                xl=2 s=5
                text="user.study_line_end"
                :value="$value?->end"
                :elements="\App\Models\Semester::all()"
                placeholder="Nincs teljesítve"
                allow-empty="Nincs teljesítve"
                helper="Csak teljesítéskor töltendő ki" />

    <x-input.button type="button" s="1" class="right red" floating icon="delete" onclick="removeStudyLine({{$index}})"/>
</div>
