@extends('auth.application.app')

@section('form')

    <div class="card">
        <form method="POST" action="{{ route('application.store', ['page' => 'questions']) }}">
            @csrf
            <div class="card-content">
                <div class="row">
                    <x-input.text s=12 id="graduation_average" text="application.graduation_average" type='number' step="0.01" min="0"
                                  text="Érettségi átlaga" :value="$user->application->graduation_average"
                                  asterisk
                                  helper='Az összes érettségi tárgy százalékos eredményének hagyományos átlaga'/>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Van lezárt egyetemi félévem",
                        'name' => 'semester_average',
                        'helper' => 'Hagyományos átlag a félév(ek)ben (két tizedesjegyre kerekítve)',
                        'optional' => true,
                        'items' => $user->application->semester_average,
                        'inputAttributes' => ['type' => 'number', 'step' => '0.01', 'min' => '0']])
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
                        <p style="margin-bottom:10px">Megpályázni kívánt státusz: <span style="color:red;" aria-label="required">*</span></p>
                        <p>
                            @php $checked = old('status') ?  old('status') == 'resident' : $user->application->applied_for_resident_status @endphp
                            <label class="black-text">
                                <input type="radio" name="status" value="resident"
                                    {{ $checked ? 'checked' : '' }}>
                                <span>@lang('role.resident')</span>
                            </label>
                        </p>
                        <p>
                            {{-- beware: the flag might be null, but must be false for this to be checked --}}
                            @php $checked = old('status') ?  old('status') == 'extern'
                                    : (false === $user->application->applied_for_resident_status) @endphp
                            <label class="black-text">
                                <input type="radio" name="status" value="extern"
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
                                Megpályázni kívánt műhely(ek): <span style="color:red;" aria-label="required">*</span>
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
                        <p style="margin-bottom:10px">Honnan hallott a Collegiumról? <span style="color:red;" aria-label="required">*</span></p>
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
                    <div>
                        <label for="question_2" style="font-size: 15px;color:black">Miért kíván a Collegium tagja lenni? (≈500-1000 leütés) <span style="color:red;" aria-label="required">*</span></label>
                        <x-input.textarea id="question_2"
                                        :value="$user->application->question_2"
                                        style="min-height:200px"
                        />
                    </div>
                    <div>
                        <label for="question_3" style="font-size: 15px;color:black">Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után? <span style="color:red;" aria-label="required">*</span></label>
                        <x-input.textarea id="question_3"
                                        :value="$user->application->question_3"
                                        style="min-height:200px"
                        />
                    </div>
                    <div>
                        <label for="question_4" style="font-size: 15px;color:black">Részt vett-e közéleti tevékenységben? Ha igen, röviden jellemezze! (Pl. diákönkormányzati tevékenység, önkéntesség, szervezeti tagság - nem kötelező)</label>
                        <x-input.textarea id="question_4"
                                        :value="$user->application->question_4"
                                        style="min-height:200px"
                        />
                    </div>
                    <x-input.textarea id="present"
                                      text="Amennyiben nem tud jelen lenni a felvételi teljes ideje alatt (kedd-péntek), kérjük itt indoklással jelezze!"
                                      :value="$user->application->present"
                                      helper="Változás esetén értesítse a titkárságot!"
                                      maxlength="5000"
                                      />
                    <x-input.checkbox id="accommodation"
                                      text="Igényel szállást a felvételi idejére?"
                                      :checked="$user->application->accommodation"/>
                    <div class="col s12">
                        <label>A szállással kapcsolatban figyelje a titkárság tájékoztatását. Az igénylés nem garantál szálláshelyet.</label>
                    </div>

                    <x-input.checkbox id="publication_consent"
                                    text="Hozzájárul ahhoz, hogy a felvételire behívottak névsorában a teljes neve és megpályázott műhelye szerepeljen?"
                                    :checked="$user->application->publication_consent"/>
                    <div id="pseudonym-wrapper">
                        <x-input.text id="pseudonym"
                                    text="Jelige"
                                    :value="$user->application->pseudonym"
                                    helper="A neve helyett a listában ez a szó fog szerepelni (5-20 ékezet nélküli nagybetű)."
                                    minlength="5"
                                    maxlength="20"
                                    pattern="^[A-Z]{5,20}$" />
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

@push('scripts')
    <script>
        const handlePseudonymVisibility = () => {
            const pseudonymWrapper = document.getElementById('pseudonym-wrapper');
            const publicationConsent = document.querySelector('input[name="publication_consent"]');
            if (!publicationConsent.checked) {
                pseudonymWrapper.style.display = 'block';
            } else {
                pseudonymWrapper.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            handlePseudonymVisibility();
            document.querySelector('input[name="publication_consent"]').addEventListener('change', handlePseudonymVisibility);
        });

        document.addEventListener('DOMContentLoaded', () => {
            let textIsDirty = false;
            document.querySelectorAll('textarea').forEach(
                (textarea) => textarea.addEventListener('input', () => {
                    textIsDirty = true;
                })
            );

            document.querySelector('button[type="submit"]').addEventListener('click', () => {
                textIsDirty = false;
            });

            window.addEventListener('beforeunload', (event) => {
                if (textIsDirty) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        });
    </script>
@endpush
