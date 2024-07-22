<div class="study_line" id="study_line_{{$index}}">
    <div class="row" style="margin:0">
        <x-input.text id="study_lines[{{ $index }}][name]"
                        xl=6
                        text="user.study_line"
                        :value="$value?->name"
                        required />
        <x-input.select id="study_lines[{{ $index }}][level]"
                        xl=2 s=6
                        text="user.study_line_level"
                        :value="$value?->type"
                        :elements="App\View\Components\Input\Select::convertArray(\App\Models\StudyLine::TYPES)"
                        required />
        <x-input.text id="study_lines[{{ $index }}][minor]"
                    xl=4 s=6
                    text="user.study_line_minor"
                    :value="$value?->minor"
		    helper="Nem kötelező"
                    />
        <x-input.select id="study_lines[{{ $index }}][start]"
                    xl=6 s=6
                    text="user.study_line_start"
                    :value="$value?->start"
                    :elements="\App\Models\Semester::all()"
                    required />
        <x-input.select id="study_lines[{{ $index }}][end]"
                    xl=5 s=5
                    text="user.study_line_end"
                    :value="$value?->end"
                    :elements="\App\Models\Semester::all()"
                    allow-empty="Nincs teljesítve"
                    helper="Csak teljesítés után töltendő ki" />

        <x-input.button type="button" s="1" class="right red tooltipped" floating icon="delete" data-tooltip="Szak törlése"  onclick="removeStudyLine({{$index}})"/>
    </div>
    <div class="divider" style="margin:10px"></div>
</div>

