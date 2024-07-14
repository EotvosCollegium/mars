@foreach ($user->roles->sortBy(function ($r, $key){return __('role.' . $r['name']);}) as $role)
    @php
    $canUpdatePermission = user()->can('updatePermission', [$user, $role, $role->pivot->workshop ?? $role->pivot->object]);
    @endphp
    <div class="row">
        <div class="col s3">@lang('role.'.$role->name)</div>
        @if($role->has_expiry_date && $canUpdatePermission)
        <form action="{{ route('users.roles.add', ['user' => $user->id, 'role'=>$role->id]) }}" method="post">
            @csrf
            <div class="col s6">
                <x-input.datepicker id='valid_until' text='user.valid_until'
                    :value="substr($role->pivot->valid_until,0,10)" />  {{-- only the date part --}}
            </div>
            <div class="col s1">
                <x-input.button floating class="left" icon="save" />
            </div>
        </form>
        @else
        <div class="col s7">
            {{ $role->pivot->translatedName }}
        </div>
        @endif
        <div class="col s2">
            @if($canUpdatePermission)
            <form action="{{ route('users.roles.delete', ['user' => $user->id, 'role' => $role->id]) }}" method="post">
                @csrf
                @method('delete')
                <input type="hidden" name="object" value="{{$role->pivot->workshop_id ?? $role->pivot->object_id}}">
                <x-input.button floating class="right red" icon="delete" />
            </form>
            @endcan
        </div>
    </div>
@endforeach
@can('updateAnyPermission', $user)
    <div class="divider" style="margin-bottom: 15px"></div>
    @foreach (\App\Models\Role::all()->sortBy(function ($r, $key){return __('role.' . $r['name']);}) as $role)
        @can('updateAnyPermission', [$user, $role])
            @if(!$user->roles->contains($role) || $role->has_objects || $role->has_workshops)
                <form action="{{ route('users.roles.add', ['user' => $user->id, 'role'=>$role->id]) }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col s3" style="padding-top: 15px">@lang('role.'.$role->name)</div>
                        <div class="col s7">
                            @if($role->has_objects)
                                <x-input.select only-input without_label :elements="$role->objects" :formatter="function($o) { return $o->translatedName; }" id="{{$role->name}}_object" name="object_id"/>
                            @elseif($role->has_workshops)
                                <x-input.select only-input without_label :elements="\App\Models\Workshop::all()" id="{{$role->name}}_workshop" name="workshop_id"/>
                            @elseif($role->has_expiry_date)
                                <x-input.datepicker id='valid_until' text='user.valid_until' />
                            @endif
                        </div>
                        <div class="col s2">
                            <x-input.button floating class="right green" icon="add" />
                        </div>
                    </div>
                </form>
            @endif
        @endcan
    @endforeach
@endcan
