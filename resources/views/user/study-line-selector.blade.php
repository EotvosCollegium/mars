<div class="study_line" id="study_line_{{$index}}">
    <div class="row" style="margin:0">
        <input type="hidden" name="study_line_indices[]" value="{{$index}}">
        <x-input.text id="study_line_name_{{ $index }}"
                        xl=6
                        text="user.study_line"
                        :value="$value?->name"
                        required />
        <x-input.select id="study_line_level_{{ $index }}"
                        xl=2 s=6
                        text="user.study_line_level"
                        :value="$value?->type"
                        :elements="App\View\Components\Input\Select::convertArray([
                            'bachelor' => 'BA/BSc',
                            'master' => 'MA/MSc',
                            'phd' => 'PhD',
                            'ot' => 'Osztatlan',
                            'other' => ' Egyéb',
                        ])"
                        required />
        <x-input.text id="study_line_minor_{{ $index }}"
                    xl=4 s=6
                    text="user.study_line_minor"
                    :value="$value?->minor"
                    helper="BTK-soknak"/>
        <x-input.select id="study_line_start_{{ $index }}"
                    xl=6 s=6
                    text="user.study_line_start"
                    :value="$value?->start"
                    :elements="\App\Models\Semester::all()"
                    required />
        <x-input.select id="study_line_end_{{ $index }}"
                    xl=5 s=5
                    text="user.study_line_end"
                    :value="$value?->end"
                    :elements="\App\Models\Semester::all()"
                    placeholder="Nincs teljesítve"
                    allow-empty="Nincs teljesítve"
                    helper="Csak teljesítéskor töltendő ki" />

        <x-input.button type="button" s="1" class="right red" floating icon="delete" onclick="removeStudyLine({{$index}})"/>
    </div>
    <div class="divider" style="margin:10px"></div>
</div>

