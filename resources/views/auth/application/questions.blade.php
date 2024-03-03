@extends('auth.application.app')

@section('questions-active')
    active
@endsection

@section('form')

    <div class="card">
        <form method="POST" action="{{ route('application.store', ['page' => 'questions']) }}">
            @csrf
            <div class="card-content">
                <div class="row">
                    <x-input.text s=12 id="graduation_average" text="application.graduation_average" type='number' step="0.01" min="0"
                                  max="5" text="Érettségi átlaga" :value="$user->application->graduation_average"
                                  required
                                  helper='Az összes érettségi tárgy hagyományos átlaga'/>
                    <div class="col s12">
                        @livewire('parent-child-form', [
                        'title' => "Van lezárt egyetemi félévem",
                        'name' => 'semester_average',
                        'helper' => 'Hagyományos átlag a félév(ek)ben',
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
                        <p style="margin-bottom:10px"><label style="font-size: 1em">Megpályázni kívánt státusz</label>
                        </p>
                        <p>
                            @php $checked = old('status') ?  old('status') == 'resident' : $user->isResident() @endphp
                            <label>
                                <input type="radio" name="status" value="resident"
                                    {{ $checked ? 'checked' : '' }}>
                                <span>@lang('role.resident')</span>
                            </label>
                        </p>
                        <p>
                            @php $checked = old('status') ?  old('status') == 'extern' : $user->isExtern() @endphp
                            <label>
                                <input type="radio" name="status" value="extern"
                                    {{ $checked ? 'checked' : '' }}>
                                <span>@lang('role.extern')</span>
                            </label>
                        </p>
                        @error('status')
                        <blockquote class="error">A státusz kitöltése kötelező</blockquote>
                        @enderror
                    </div>
                    <div class="input-field col s12"><p style="margin-bottom:10px"><label style="font-size: 1em">Honnan
                                hallott a Collegiumról?</label></p>
                        @foreach(\App\Models\ApplicationForm::QUESTION_1 as $answer)
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
                            <x-input.textarea only-input id="question_1_other" name="question_1[]"
                                          without-label placeholder="egyéb/bővebben...">{{$user->application->question_1_custom}}</x-input.textarea>
                        </div>
                    </div>
                    <x-input.textarea id="question_2" text="Miért kíván a Collegium tagja lenni?"
                                      helper="≈300-500 karakter">{{$user->application->question_2}}</x-input.textarea>
                    <x-input.textarea id="question_3"
                                      text="Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?"
                                      >{{$user->application->question_3}}</x-input.textarea>
                    <x-input.textarea id="question_4"
                                      text="Részt vett-e közéleti tevékenységben? Ha igen, röviden jellemezze!"
                                      helper="Pl. diákönkormányzati tevékenység, önkéntesség, szervezeti tagság. (nem kötelező)"
                                      >{{$user->application->question_4}}</x-input.textarea>
                    <x-input.textarea id="present"
                                      text="Amennyiben nem tud jelen lenni a felvételi teljes ideje alatt (vasárnap-szerda), kérjük itt indoklással jelezze!"
                                      helper="Változás esetén értesítse a titkárságot!">{{$user->application->present}}</x-input.textarea>
                    <x-input.checkbox id="accommodation"
                                      text="Igényel-e szállást a felvételi idejére?"
                                      :checked="$user->application->accommodation"/>
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
