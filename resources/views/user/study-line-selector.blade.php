<div class="study_line" id="study_line_{{$index}}">
    <div class="row" style="margin:0">
        <x-input.text id="study_lines[{{ $index }}][name]"
                        xl=4
                        text="user.study_line"
                        :value="$value?->name"
                        :required="$user->isCollegist(alumni: true)"
                        asterisk
                        :disabled="user()->cannot('edit', $user)"
                        maxlength="255"
                        />
        <x-input.select id="study_lines[{{ $index }}][type]"
                        xl=2 s=6
                        text="user.study_line_level"
                        :value="$value?->type"
                        :elements="\App\Models\StudyLine::TYPES"
                        :required="$user->isCollegist(alumni: true)"
                        asterisk
                        :disabled="user()->cannot('edit', $user)"
                        />
        <x-input.text id="study_lines[{{ $index }}][training_code]"
                    xl=2 s=6
                    text="user.study_line_training_code"
                    :value="$value?->training_code"
                    :required="$user->isCollegist(alumni: true)"
                    helper="Pl. TTK-FIZIKA-NBHU"
                    asterisk
                    :disabled="user()->cannot('edit', $user)"
                    maxlength="255"
                    />
        <x-input.text id="study_lines[{{ $index }}][minor]"
                    xl=3 s=6
                    text="user.study_line_minor"
                    :value="$value?->minor"
		            helper="Nem kötelező"
                    :disabled="user()->cannot('edit', $user)"
                    maxlength="255"
                    />
        <x-input.select id="study_lines[{{ $index }}][start]"
                    xl=6 s=6
                    text="user.study_line_start"
                    :value="$value?->start"
                    :elements="\App\Models\Semester::allUntilCurrent()->concat([\App\Models\Semester::next()])"
                    :required="$user->isCollegist(alumni: true)"
                    asterisk
                    :disabled="user()->cannot('edit', $user)"
                    />
        <x-input.select id="study_lines[{{ $index }}][end]"
                    xl=5 s=5
                    text="user.study_line_end"
                    :value="$value?->end"
                    :elements="\App\Models\Semester::allUntilCurrent()"
                    allow-empty="Nincs teljesítve"
                    helper="Csak teljesítés után töltendő ki"
                    :disabled="user()->cannot('edit', $user)"
                    />
        @can('edit', $user)
        <x-input.button type="button" s="1" class="right red tooltipped" floating icon="delete" data-tooltip="Szak törlése"  onclick="removeStudyLine({{$index}})"/>
        @endcan
    </div>
    <div class="divider" style="margin:10px"></div>
</div>

