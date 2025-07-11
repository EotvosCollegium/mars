<form method="POST" action="{{ route('users.update.educational', ['user' => $user]) }}">
    @csrf
    @if (user()->cannot('edit', $user))
        @markdown(__('user.data_cannot_be_edited_application'),
            [
                'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
            ]
        )
    @elseif(user()->cannot('editStaticEducationalInformation', $user))
        @markdown(__('user.some_data_cannot_be_edited'),
            [
                'SYSADMIN_EMAIL' => config('mail.sys_admin_mail'),
                'SECRETARIAT_EMAIL' => config('mail.secretary_mail'),
                'STUDENT_COUNCIL_EMAIL' => config('contacts.mail_valasztmany'),
            ]
        )
    @endif
    @if($application ?? false)
        <blockquote>
            <p>Az egyetemi e-mail-cím a felvételi eljárást követően is pótolható.</p>
        </blockquote>
    @endif
    <div class="row">
        <x-input.text id="high_school" text="user.high_school"
                      :value="$user->educationalInformation?->high_school"
                      :required="$user->isCollegist()"
                      :disabled="user()->cannot('editStaticEducationalInformation', $user)"
                      asterisk
                      />
        <x-input.text s=12 m=6 id="year_of_graduation" text="user.year_of_graduation" type='number' min="1895"
                      :max="date('Y')"
                      :value="$user->educationalInformation?->year_of_graduation"
                      :required="$user->isCollegist()"
                      :disabled="user()->cannot('editStaticEducationalInformation', $user)"
                      asterisk
                      />
        <x-input.text s=12 m=6 id="year_of_acceptance" text="user.year_of_acceptance" type='number' min="1895"
                        :max="date('Y')"
                        :value="$user->educationalInformation?->year_of_acceptance"
                        :required="$user->isCollegist()"
                        :disabled="user()->cannot('editStaticEducationalInformation', $user)"
                        asterisk
                    />
        <x-input.text s=6 id="neptun" text="user.neptun"
                        :value="$user->educationalInformation?->neptun"
                        :required="$user->isCollegist()"
                        :disabled="user()->cannot('editStaticEducationalInformation', $user)"
                        asterisk
                        />
        <x-input.text s=6 id='educational-email' text='user.educational-email' name="email"
                        :value="$user->educationalInformation?->email"
                        :helper="
                        isset($application)
                        ?
                            'lehetőleg @student.elte.hu-s (nem kötelező, a felvételit követően pótolható)'
                        :
                            'lehetőleg @student.elte.hu-s'"
                        :required="$user->isCollegist()"
                        :disabled="user()->cannot('edit', $user)"
                        :asterisk="!isset($application)"
                        />

        <div class="input-field col s12 m6">
            <p style="margin-bottom:10px">@lang('user.faculty'): <span style="color:red;">*</span></p>
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
        @if(!isset($application))
            <div class="input-field col s12 m6">
                <p style="margin-bottom:10px">
                    @lang('user.workshops'): <span style="color:red;">*</span>
                </p>
                @foreach ($workshops as $workshop)
                    <p>
                        @php $checked = $user->workshops->contains($workshop->id) @endphp
                        <x-input.checkbox only_input :text="$workshop->name" id="workshop{{$workshop->id}}" name="workshop[]"
                                          value="{{ $workshop->id }}" :checked='$checked'/>
                    </p>
                @endforeach
                @error('workshop')
                <blockquote class="error">@lang('user.workshop_must_be_filled')</blockquote>
                @enderror
            </div>
        @endif
    </div>
    @foreach($user->educationalInformation?->studyLines ?? [] as $studyLine)
        @include('user.study-line-selector', ['index' => $loop->index, 'value' => $studyLine])
    @endforeach
    <x-input.button type="button" id="addStudyLine" floating icon="add" class="tooltipped" data-tooltip="Szak hozzáadása" onclick="insertEmptyStudyLine()" />
    {{-- hiding these fields from applications; they are not relevant there --}}
    @if(\Route::current()->getName() != 'application')
    <x-input.textarea
            id='research_topics'
            text='user.research_topics'
            :value="$user->educationalInformation?->research_topics" />
    <x-input.textarea
        id='extra_information'
        text='user.extra_information'
        :value="$user->educationalInformation?->extra_information" />
    @endif
    <div class="row" style="margin: 0">
            <x-input.button class="right" text="general.save" />
    </div>
</form>

@push('scripts')
<script>
function removeStudyLine(index) {
    if($('.study_line').length > 1){
        $("#study_line_" + index).remove();
    } else {
        M.toast({html: 'Legalább egy szakot meg kell adni!'});
    }

}
let studyLineCounter = {{$user->educationalInformation?->studyLines?->count() ?? 0}};
$(document).ready(function(){
    if(studyLineCounter == 0) {
        insertEmptyStudyLine();
    }
  });
function insertEmptyStudyLine() {
    let index = studyLineCounter++;
    let text = `
    @include('user.study-line-selector', ['index' => '.index.', 'value' => null])
    `
    $(text.replace(/.index./g, index)).insertBefore('#addStudyLine');
    $('select').formSelect();
}
</script>
@endpush
