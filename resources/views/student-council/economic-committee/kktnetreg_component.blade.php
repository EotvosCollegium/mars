<div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">@lang('general.filter')</span>
            @foreach (\App\Models\Workshop::all() as $w)
                @if(in_array($w->id, $this->workshops))
                    <span class="new badge {{ $w->color() }}" data-badge-caption=""
                          style="float:none;padding:2px 2px 2px 5px;margin:2px;cursor:pointer;"
                          wire:click="deleteWorkshop({{$w->id}})">
                    <nobr><i>{{ $w->name }}</i> &cross;</nobr>
                </span>
                @else
                    <span class="new badge {{ $w->color() }}" data-badge-caption=""
                          style="float:none;padding:2px 2px 2px 5px;margin:2px;cursor:pointer"
                          wire:click="addWorkshop({{$w->id}})">
                    <nobr>{{ $w->name }}</nobr>
                </span>
                @endif
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            {{-- List --}}
            <div class="row">
                <div class="col s12 xl7">
                    <span class="card-title">@lang('checkout.have_not_paid') ({{ \App\Models\Semester::current()->tag }}) </span>
                </div>
            </div>
            @forelse($this->unpaidUsers as $user)
                @can('view', $user)
                    <div class="row">
                        <div class="col s12 xl3">
                            <a href="{{ route('users.show', ['user' => $user->id]) }}"><b>{{ $user->name }}</b></a><br>
                            {{ $user->email }}
                            @if($user->hasEducationalInformation())
                                <br>{{ $user->educationalInformation->neptun ?? '' }}
                            @endif
                        </div>
                        <div class="col s12 xl4">
                            @if($user->hasEducationalInformation())
                                @include('user.workshop_tags', ['user' => $user, 'newline' => true])
                            @endif
                        </div>
                    </div>
                @endcan
            @empty
            @lang('user.no_such_user')
            @endforelse
            <div class="row">
                <div class="col s12">
                    <div class="divider"></div>
                    <div class="right"><i><b>{{$this->unpaidUsers->count()}} @lang('user.users_amount')</i></b></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            {{-- List --}}
            <div class="row">
                <div class="col s12 xl7">
                    <span class="card-title">@lang('checkout.payments') ({{\App\Models\Semester::current()->tag}})</span>
                </div>
            </div>
            @forelse($this->payments as $transaction)
                @php($user = $transaction->payer)
                @can('view', $user)
                    <div class="row">
                        <div class="col s12 xl3">
                            <a href="{{ route('users.show', ['user' => $user->id]) }}"><b>{{ $user->name }}</b></a><br>
                            {{ $user->email }}
                            @if($user->hasEducationalInformation())
                                <br>{{ $user->educationalInformation->neptun ?? '' }}
                            @endif
                        </div>
                        <div class="col s12 xl4">
                            @if($user->hasEducationalInformation())
                                @include('user.workshop_tags', ['user' => $user, 'newline' => true])
                            @endif
                        </div>
                        <div class="col s12 xl1" style="padding-left:50px;">
                            {{ $transaction->comment }}
                            <br>
                            {{ $transaction->amount }}
                        </div>

                    </div>
                @endcan
            @empty
            @lang('checkout.no_such_payment')
            @endforelse
            <div class="row">
                <div class="col s12">
                    <div class="divider"></div>
                    <div class="right"><i><b>{{$this->payments->count()}} @lang('checkout.payments_amount')</i></b></div>
                </div>
            </div>
        </div>
    </div>
</div>