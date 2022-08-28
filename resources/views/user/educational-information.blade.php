<form method="POST" action="{{ route('users.update.educational', ['user' => $user]) }}">
    @csrf
    <div class="row">
        <x-input.text id='high_school' locale='user'
                      :value="$user->educationalInformation?->high_school"
                      required/>
        <x-input.text s=12 m=6 id='year_of_graduation' locale='user' type='number' min="1895"
                      :max="date('Y')"
                      :value="$user->educationalInformation?->year_of_graduation"
                      required/>
        @if(!($application ?? false))
            <x-input.text s=12 m=6 id='year_of_acceptance' locale='user' type='number' min="1895"
                          :max="date('Y')"
                          :value="$user->educationalInformation?->year_of_acceptance"
                          required/>
        @else
            <x-input.text s=12 m=6 id='year_of_acceptance' text='Felvételi éve' type='number'
                          :value="date('Y')" disabled
                          required/>
        @endif
        <x-input.text s=6 id='neptun' locale='user'
                      :value="$user->educationalInformation?->neptun"
                      required/>
        <x-input.text s=6 id='educational-email' locale='user' name="email"
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

        <div class="col s12">
            @livewire('parent-child-form', ['title' => "Szak(ok)", 'name' => 'program', 'items' =>
            $user->educationalInformation ? $user->educationalInformation->program : null])
        </div>

        <x-input.button class="right" text="general.save" />
    </div>
</form>
