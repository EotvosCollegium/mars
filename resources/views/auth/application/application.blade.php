{{-- All the application's data for finalization and for the admission committes to show --}}
@can('viewApplication', $user)
    <div class="card">
        <div class="card-content">
            <div class="row" style="margin-bottom: 0">
                <div class="col s12 xl4">
                    @if ($user->profilePicture)
                        <img src="{{ url($user->profilePicture->path) }}" style="max-width:100%">
                    @else
                        <span style="font-style:italic;color:red">hiányzó profilkép</span>
                    @endif
                </div>
                <div class="col s12 xl8">
                    @can('viewAnyApplication', \App\Models\User::class)
                        @if(!auth()->user()->hasRole(\App\Models\Role::SYS_ADMIN) || $user->application->status == \App\Models\ApplicationForm::STATUS_IN_PROGRESS)
                            <span class="right">
                                @include('auth.application.status', ['status' => $user->application->status])
                            </span>
                        @else
                            @if($user->application->status != \App\Models\ApplicationForm::STATUS_IN_PROGRESS)
                                @livewire('application-status-update', ['application' => $user->application])
                            @endif
                        @endif
                    @endcan
                    <div class="card-title">{{ $user->name }}</div>
                    <p style="margin-bottom: 5px"><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                    <p style="margin-bottom: 5px">{{ $user->personalInformation->phone_number }}</p>
                    <p style="margin-bottom: 5px">
                        @if($user->educationalInformation && $user->educationalInformation->programs)
                            {{ $user->educationalInformation->programs }}
                        @else
                            <span style="font-style:italic;color:red">hiányzó szak</span>
                        @endif
                    </p>
                    <p style="margin-bottom: 5px">
                        @if ($user->workshops->count() > 0)
                            @include('user.workshop_tags', ['user' => $user, 'newline' => true])
                        @else
                            <span style="font-style:italic;color:red">hiányzó műhely</span>
                        @endif
                    </p>
                    <p>
                        @if ($user->isResident())
                            <span class="new badge coli blue tag" style="float:none;padding:4px;margin:0 10px 0px 2px;"
                                  data-badge-caption="">
                            @lang('role.resident')
                        </span>
                        @endif
                        @if ($user->isExtern())
                            <span class="new badge coli orange tag"
                                  style="float:none;padding:4px;margin:0 10px 0px 2px;"
                                  data-badge-caption="">
                            @lang('role.extern')
                        </span>
                        @endif
                        @if(!$user->isResident() && !$user->isExtern())
                            <span style="font-style:italic;color:red">hiányzó státusz</span>
                        @endif
                    </p>
                </div>
                @if($expanded ?? true)
                    <div class="col s12">
                        <table>
                            <tbody>
                            <tr>
                                <th scope="row">@lang('user.place_and_date_of_birth')</th>
                                <td>
                                    {{ $user->personalInformation->place_of_birth }}
                                    {{ $user->personalInformation->date_of_birth }}
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">@lang('user.mothers_name')</th>
                                <td>
                                    {{ $user->personalInformation->mothers_name }}
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">@lang('user.address')</th>
                                <td>
                                    {{ $user->personalInformation->country }},
                                    {{ $user->personalInformation->county }}
                                    <br>
                                    {{ $user->personalInformation->zip_code }} {{ $user->personalInformation->city }},
                                    {{ $user->personalInformation->street_and_number }}
                                </td>
                            </tr>
                            @if($user->educationalInformation)
                                <tr>
                                    <th scope="row">@lang('user.high_school')</th>
                                    <td>
                                        {{ $user->educationalInformation->high_school }}<br>
                                        @if(!$user->educationalInformation->high_school)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">@lang('user.neptun')</th>
                                    <td>
                                        {{ $user->educationalInformation->neptun }}
                                        @if(!$user->educationalInformation->neptun)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">@lang('user.educational-email')</th>
                                    <td>
                                        {{ $user->educationalInformation->email }}
                                        @if(!$user->educationalInformation->email)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <th>Tanulmányi adatok</th>
                                    <td><span style="font-style:italic;color:red">hiányzó adatok</span></td>
                                </tr>
                            @endif
                            <tr>
                                <th scope="row">@lang('user.faculty')</th>
                                <td>
                                    @forelse ($user->faculties ?? [] as $faculty)
                                        {{ $faculty->name }}<br>
                                    @empty
                                        <span style="font-style:italic;color:red">hiányzó adat</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">@lang('user.year_of_graduation')</th>
                                <td>
                                    {{ $user->educationalInformation ? $user->educationalInformation->year_of_graduation : '' }}
                                    @if(!$user->educationalInformation || !$user->educationalInformation->year_of_graduation)
                                        <span style="font-style:italic;color:red">hiányzó adat</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Érettségi átlaga</th>
                                <td>
                                    {{ $user->application->graduation_average }}
                                    @if(!$user->application->graduation_average)
                                        <span style="font-style:italic;color:red">hiányzó adat</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Előző szemeszterek átlaga</th>
                                <td>
                                    @forelse ($user->application->semester_average ?? [] as $key => $avg)
                                        {{ $key + 1 }}. félév: {{ $avg }}<br>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Nyelvvizsga</th>
                                <td>
                                    @forelse ($user->application->language_exam ?? [] as $item)
                                        {{ $item }}<br>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Versenyeredmények</th>
                                <td>
                                    @forelse ($user->application->competition ?? [] as $item)
                                        {{ $item }}<br>
                                    @empty
                                        -
                                    @endforelse

                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Publikációk</th>
                                <td>
                                    @forelse ($user->application->publication ?? [] as $item)
                                        {{ $item }}<br>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Külföldi tanulmányok</th>
                                <td>
                                    @forelse ($user->application->foreign_studies ?? [] as $item)
                                        {{ $item }}<br>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p style="font-weight: bold;">Honnan hallott a Collegiumról?</p>
                                    <p>
                                        {{ implode(", ", $user->application->question_1 ?? []) }}
                                        @if(!$user->application->question_1)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p style="font-weight: bold;">Miért kíván a Collegium tagja lenni?</p>
                                    <p>
                                        {{ $user->application->question_2 }}
                                        @if(!$user->application->question_2)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p style="font-weight: bold;">Tervez-e tovább tanulni a diplomája
                                        megszerzése
                                        után?
                                        Milyen tervei vannak az egyetem után?</p>
                                    <p>
                                        {{ $user->application->question_3}}
                                        @if(!$user->application->question_3)
                                            <span style="font-style:italic;color:red">hiányzó adat</span>
                                        @endif
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p style="font-weight: bold;">Részt vett-e közéleti tevékenységben? Ha
                                        igen, röviden jellemezze!</p>
                                    <p>{{ $user->application->question_4 ?? "-" }}</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Csatolmányok</th>
                                <td>
                                    @forelse ($user->application->files ?? [] as $file)
                                        @if (!$loop->first)
                                            <div class="divider"></div>
                                        @endif
                                        <div class="row" style="margin-bottom: 0; padding: 10px">
                                            <div class="col" style="margin-top: 5px">
                                                <a href="{{ url($file->path) }}" target="_blank">{{ $file->name }}</a>
                                            </div>
                                        </div>
                                    @empty
                                        <span style="font-style:italic;color:red">hiányzó adat</span>
                                    @endforelse
                                    @if(count($user->application->files ?? []) > 0 && count($user->application->files ?? []) < 2)
                                        <span
                                            style="font-style:italic;color:red">legalább 2 fájlt fel kell tölteni</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">A felvételi teljes időtartamában itt lesz?</th>
                                <td>
                                    {{ $user->application->present }}
                                    @if(!$user->application->present)
                                        <span style="color:green">Igen</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Igényel szállást?</th>
                                <td>
                                    @if($user->application->accommodation)
                                        <span style="color:green">Igen</span>
                                    @else
                                        Nem
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endcan


