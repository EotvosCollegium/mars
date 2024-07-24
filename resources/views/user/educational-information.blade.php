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
            <p style="margin-bottom:10px">@lang('user.faculty'):</p>
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
                    @lang('user.workshops'):
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
