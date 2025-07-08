@extends('auth.application.app')

@section('form')

    <div class="card">
        <form method="POST" action="{{ route('application.store', ['page' => 'questions']) }}">
            @csrf
            <div class="card-content">
                <div class="row">
                    <x-input.text s=12 id="graduation_average" text="application.graduation_average" type='number' step="0.01" min="0"
                                  text="Érettségi átlaga" :value="$user->application->graduation_average"
                                  required
                                  helper='Az összes érettségi tárgy hagyományos átlaga'/>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Van lezárt egyetemi félévem",
                        'name' => 'semester_average',
                        'helper' => 'Hagyományos átlag a félév(ek)ben (tizedesponttal)',
                        'optional' => true,
                        'items' => $user->application->semester_average])
                    </div>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Van versenyeredményem",
                        'name' => 'competition',
                        'helper' => 'Verseny, elért eredmény, év',
                        'optional' => true,
                        'items' => $user->application->competition])
                    </div>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Van publikációm",
                        'name' => 'publication',
                        'helper' => 'Név, kiadó, társszerző (ha van), év',
                        'optional' => true,
                        'items' => $user->application->publication])
                    </div>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Tanultam külföldön",
                        'name' => 'foreign_studies',
                        'helper' => 'Intézmény, képzés, időtartam',
                        'optional' => true,
                        'items' => $user->application->foreign_studies])
                    </div>
                    <div class="input-field col s12">
                        <p style="margin-bottom:10px">Megpályázni kívánt státusz:</p>
                        <p>
                            @php $checked = old('status') ?  old('status') == 'resident' : $user->application->applied_for_resident_status @endphp
                            <label class="black-text">
                                <input type="radio" name="status" value="resident" required
                                    {{ $checked ? 'checked' : '' }}>
                                <span>@lang('role.resident')</span>
                            </label>
                        </p>
                        <p>
                            {{-- beware: the flag might be null, but must be false for this to be checked --}}
                            @php $checked = old('status') ?  old('status') == 'extern'
                                    : (false === $user->application->applied_for_resident_status) @endphp
                            <label class="black-text">
                                <input type="radio" name="status" value="extern" required
                                    {{ $checked ? 'checked' : '' }}>
                                <span>@lang('role.extern')</span>
                            </label>
                        </p>
                        @error('status')
                        <blockquote class="error">A státusz kitöltése kötelező</blockquote>
                        @enderror
                    </div>
                    <div class="input-field col s12">
                        <p style="margin-bottom:10px">
                                Megpályázni kívánt műhely(ek):
                        </p>
                        <div class="row">
                        @foreach ($workshops as $workshop)
                            <div class="col s6">
                                @php $checked = $user->application->appliedWorkshops->contains($workshop->id) @endphp
                                <x-input.checkbox only_input id="workshop_{{$workshop->id}}" :text="$workshop->name" name="workshop[]"
                                                  value="{{ $workshop->id }}" checked='{{$checked}}'/>
                            </div>
                        @endforeach
                        </div>
                        @error('workshop')
                        <blockquote class="error">@lang('user.workshop_must_be_filled')</blockquote>
                        @enderror
                        <blockquote>
                            Kérjük, jelentkezését csak olyan műhelyekbe adja be, amelyek munkájában szakmailag részt tud venni. A műhelyek egymástól függetlenül dönthetnek a meghallgatásáról.
                        </blockquote>
                    </div>
                    <div class="input-field col s12">
                        <p style="margin-bottom:10px">Honnan hallott a Collegiumról?</p>
                        @foreach(\App\Models\Application::QUESTION_1 as $answer)
                            @if(in_array($answer, $user->application->question_1 ?? []) !== false)
                                <p>
                                    <x-input.checkbox
                                        only-input
                                        :id="'question_1_'.$loop->index"
                                        :value="$answer"
                                        name="question_1[]"
                                        :text="$answer"
                                        checked
                                    />
                                </p>
                            @else
                                <p>
                                    <x-input.checkbox
                                        only-input
                                        :id="'question_1_'.$loop->index"
                                        :value="$answer"
                                        name="question_1[]"
                                        :text="$answer"
                                    />
                                </p>
                            @endif
                        @endforeach
                        <div class="input-field" style="margin: 0; padding-left:35px">
                            <x-input.text only-input id="question_1_other"
                                          :value="$user->application->question_1_custom" name="question_1[]"
                                          without-label placeholder="egyéb/bővebben..."/>
                        </div>
                    </div>
                    <div class="markdown_with_red">
                        @markdown(\App\Models\ConfigurableText::getText("APPLICATION_QUESTION2"))
                        <x-input.textarea id="question_2" :helper="\App\Models\ConfigurableText::getText('APPLICATION_QUESTION2_HELPER')"
                                          :value="$user->application->question_2"/>
                    </div>
                    <div class="markdown_with_red">
                        @markdown(\App\Models\ConfigurableText::getText("APPLICATION_QUESTION3"))
                        <x-input.textarea id="question_3" :helper="\App\Models\ConfigurableText::getText('APPLICATION_QUESTION3_HELPER')"
                                          :value="$user->application->question_3"/>
                    </div>
                    <div class="markdown_with_red">
                        @markdown(\App\Models\ConfigurableText::getText("APPLICATION_QUESTION4"))
                        <x-input.textarea id="question_4" :helper="\App\Models\ConfigurableText::getText('APPLICATION_QUESTION4_HELPER')"
                                          :value="$user->application->question_4"/>
                    </div>
                    <div class="markdown_with_red">
                        Amennyiben nem tud jelen lenni a felvételi teljes ideje alatt (kedd-péntek), kérjük itt indoklással jelezze!
                        <x-input.textarea id="present" :value="$user->application->present"  helper="Változás esetén értesítse a titkárságot!"/>
                    </div>
                    <x-input.checkbox id="accommodation"
                                      text="Igényel-e szállást a felvételi idejére?"
                                      :checked="$user->application->accommodation"/>
                    <div class="col s12">
                        <label>A szállással kapcsolatban figyelje a titkárság tájékoztatását. Az igénylés nem garantál szálláshelyet.</label>
                    </div>

                </div>


            </div>
            <div class="card-action">
                <div class="row" style="margin-bottom: 0">
                    <x-input.button only_input class="right" text="general.save"/>
                </div>
            </div>
        </form>
    </div>

@endsection
