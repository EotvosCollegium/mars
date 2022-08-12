@extends('auth.application.app')

@section('educational-active')
    active
@endsection

@section('form')

    <div class="card">
        <form method="POST" action="{{ route('application.store', ['page' => 'educational']) }}">
            @csrf
            <div class="card-content">
                <div class="row">
                    <x-input.text s=12 m=6 id='high_school' locale='user'
                                  :value="$user->educationalInformation ? $user->educationalInformation->high_school : null"
                                  required/>
                    <x-input.text s=12 m=6 id='year_of_graduation' locale='user' type='number' min="1895"
                                  :max="date('Y')"
                                  :value="$user->educationalInformation ? $user->educationalInformation->year_of_graduation : null"
                                  required/>

                    <x-input.text s=6 id='neptun' locale='user'
                                  :value="$user->educationalInformation ? $user->educationalInformation->neptun : null"
                                  required/>
                    <x-input.text s=6 id='educational_email' locale='user'
                                  :value="$user->educationalInformation ? $user->educationalInformation->email : null"
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
                    <x-input.text s=12 id='graduation_average' locale='application' type='number' step="0.01" min="0"
                                  max="5" text="Érettségi átlaga" :value="$user->application->graduation_average"
                                  required
                                  helper='Az összes érettségi tárgy hagyományos átlaga'/>
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', ['title' => "Szak(ok)", 'name' => 'programs', 'items' =>
                    $user->educationalInformation ? $user->educationalInformation->program : null])
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', [
                    'title' => "Van lezárt egyetemi félévem",
                    'name' => 'semester_average',
                    'helper' => 'Hagyományos átlag a félév(ek)ben',
                    'optional' => true,
                    'items' => $user->application->semester_average])
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', [
                    'title' => "Van nyelvvizsgám",
                    'name' => 'language_exam',
                    'helper' => 'Nyelv, szint, fajta',
                    'optional' => true,
                    'items' => $user->application->language_exam])
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', [
                    'title' => "Van versenyeredményem",
                    'name' => 'competition',
                    'helper' => 'Verseny, elért eredmény, év',
                    'optional' => true,
                    'items' => $user->application->competition])
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', [
                    'title' => "Van publikációm",
                    'name' => 'publication',
                    'helper' => 'Név, kiadó, társszerző (ha van), év',
                    'optional' => true,
                    'items' => $user->application->publication])
                </div>
                <div class="row" style="margin:0">
                    @livewire('parent-child-form', [
                    'title' => "Tanultam külföldön",
                    'name' => 'foreign_studies',
                    'helper' => 'Intézmény, képzés, időtartam',
                    'optional' => true,
                    'items' => $user->application->foreign_studies])
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
