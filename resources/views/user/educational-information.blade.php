<form method="POST" action="{{ route('users.update.educational', ['user' => $user]) }}">
    @csrf
    <div class="row">
        <x-input.text id="high_school" text="user.high_school"
                      :value="$user->educationalInformation?->high_school"
                      required/>
        <x-input.text s=12 m=6 id="year_of_graduation" text="user.year_of_graduation" type='number' min="1895"
                      :max="date('Y')"
                      :value="$user->educationalInformation?->year_of_graduation"
                      required/>
        @if(!($application ?? false))
            <x-input.text s=12 m=6 id="year_of_acceptance" text="user.year_of_acceptance" type='number' min="1895"
                          :max="date('Y')"
                          :value="$user->educationalInformation?->year_of_acceptance"
                          required/>
        @else
            <x-input.text s=12 m=6 id='year_of_acceptance' text='Collegiumi felvételi éve' type='number'
                          :value="date('Y')" disabled/>
            <input type="hidden" name="year_of_acceptance" value="{{date('Y')}}"/>
        @endif
        <x-input.text s=6 id="neptun" text="user.neptun"
                      :value="$user->educationalInformation?->neptun"
                      required/>
        <x-input.text s=6 id='educational-email' text='user.educational-email' name="email"
                      :value="$user->educationalInformation?->email"
                      required helper="lehetőleg @student.elte.hu-s"/>

        <div class="input-field col s12 m6">
            <p style="margin-bottom:10px"><label style="font-size: 1em">@lang('user.faculty')</label></p>
            @foreach ($faculties as $faculty)
                <p>
                    @php $checked = old('faculty') !== null && in_array($faculty->id, old('faculty')) || in_array($faculty->id, $user->faculties->pluck('id')->toArray()) @endphp
                    <x-input.checkbox only_input :text="$faculty->name" name="faculty[]"
                                      value="{{ $faculty->id }}" :checked='$checked'/>
                </p>
            @endforeach
            @error('faculty')
            <blockquote class="error">@lang('user.faculty_must_be_filled')</blockquote>
            @enderror
        </div>
        <div class="input-field col s12 m6">
            <p style="margin-bottom:10px">
                <label style="font-size: 1em">
                    @if($application ?? false)
                        Megpályázni kívánt műhely(ek)
                    @else
                        @lang('user.workshops')
                    @endif
                </label>
            </p>
            </p>
            @foreach ($workshops as $workshop)
                <p>
                    @php $checked = old('workshop') !== null && in_array($workshop->id, old('workshop')) || in_array($workshop->id, $user->workshops->pluck('id')->toArray()) @endphp
                    <x-input.checkbox only_input :text="$workshop->name" name="workshop[]"
                                      value="{{ $workshop->id }}" :checked='$checked'/>
                </p>
            @endforeach
            @error('workshop')
            <blockquote class="error">@lang('user.workshop_must_be_filled')</blockquote>
            @enderror
        </div>
    </div>
    @foreach($user->educationalInformation?->studyLines ?? [] as $program)
        <div class="row program" id="programme_{{$loop->index}}" style="margin:0">
            <input type="hidden" name="study_line_index[]" value="{{$loop->index}}">
            <x-input.text id="study_line_name_{{ $loop->index }}"
                            s=4
                            text="user.study_line"
                            :value="$program->name"
                            required />
            <x-input.select id="study_line_level_{{ $loop->index }}"
                            s=3
                            text="user.study_line_level"
                            :value="$program->type"
                            :elements="App\View\Components\Input\Select::convertArray([
                                'bachelor' => 'BA/BSc',
                                'master' => 'MA/Msc',
                                'phd' => 'Phd',
                                'ot' => 'OT',
                                'other' => ' Egyéb',
                            ])"
                            required />
            <x-input.select id="study_line_start_{{ $loop->index }}"
                        s=2
                        text="user.study_line_start"
                        :value="$program->start"
                        :elements="\App\Models\Semester::all()"
                        required />
            <x-input.select id="study_line_end_{{ $loop->index }}"
                        s=2
                        text="user.study_line_end"
                        :value="$program->end"
                        :elements="\App\Models\Semester::all()"
                        helper="Nem kötelező mező" />

            <x-input.button type="button" s="1" class="right red" floating icon="delete" onclick="removeProgram({{$loop->index}})"/>
        </div>
    @endforeach
    <x-input.button type="button" id="addProgram" floating icon="add" onclick="insertEmptyProgram()" />
    <div class="row" style="margin: 0">
            <x-input.button class="right" text="general.save" />
    </div>
</form>

@push('scripts')
<script>
function removeProgram(index) {
    console.log($('.program').length)
    if($('.program').length > 1){
        $("#programme_" + index).remove();
    } else {
        M.toast({html: 'Legalább egy szakot meg kell adni!'});
    }

}

let programCounter = {{$user->educationalInformation?->studyLines?->count() ?? 0}};
$(document).ready(function(){
    if(programCounter == 0) {
        insertEmptyProgram();
    }
  });
function insertEmptyProgram() {
    let index = programCounter++;
    let text = `
    <div class="row program" id="programme_`+index+`" style="margin:0">
            <input type="hidden" name="study_line_index[]" value="`+index+`">
            <x-input.text id="study_line_name_`+index+`"
                s=4
                text="user.study_line" />
            <x-input.select id="study_line_level_`+index+`"
                            s=3
                            text="user.study_line_level"
                            :elements="App\View\Components\Input\Select::convertArray([
                                'bachelor' => 'BA/BSc',
                                'master' => 'MA/Msc',
                                'phd' => 'Phd',
                                'ot' => 'OT',
                                'other' => _('general.other'),
                            ])" />
            <x-input.select id="study_line_start_`+index+`"
                        s=2
                        text="user.study_line_start"
                        :elements="\App\Models\Semester::all()" />
            <x-input.select id="study_line_end_`+index+`"
                        s=2
                        text="user.study_line_end"
                        :elements="\App\Models\Semester::all()"
                        helper="Nem kötelező mező" />
            <x-input.button  type="button" s="1" class="right red" floating icon="delete" onclick="removeProgram(`+ index +`)" />
        </div>
    `
    $(text).insertBefore('#addProgram');
    $('select').formSelect();
}
</script>
@endpush
