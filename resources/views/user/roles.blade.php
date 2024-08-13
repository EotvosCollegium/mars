@foreach ($user->roles->sortBy(function ($r, $key){return __('role.' . $r['name']);}) as $role)
    <div class="row">
        <div class="col s4">@lang('role.'.$role->name)</div>
        <div class="col s4">
            {{ $role->pivot->translatedName }}
        </div>
        <div class="col s4">
            @can('updatePermission', [$user, $role, $role->pivot->workshop ?? $role->pivot->object])
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
    @foreach (App\Models\Role::all()->sortBy(function ($r, $key){return __('role.' . $r['name']);}) as $role)
        @can('updateAnyPermission', [$user, $role])
            @if(!$user->roles->contains($role) || $role->has_objects || $role->has_workshops)
                <form action="{{ route('users.roles.add', ['user' => $user->id, 'role'=>$role->id]) }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col s4" style="padding-top: 15px">@lang('role.'.$role->name)</div>
                        <div class="col s6">
                            @if($role->has_objects)
                                @php
                                $objects_to_assign = $role->objects->filter(function ($object) use ($user, $role) {
                                    return user()->can('updatePermission', [$user, $role, $object]);
                                })
                                @endphp
                                <x-input.select
                                    only-input
                                    without_label
                                    :elements="$objects_to_assign"
                                    :formatter="function($o) { return $o->translatedName; }"
                                    id="{{$role->name}}_object"
                                    name="object_id"
                                />
                            @elseif($role->has_workshops)
                                @php
                                    $workshops_to_assign = \App\Models\Workshop::all()->filter(function ($workshop) use ($user, $role) {
                                        return user()->can('updatePermission', [$user, $role, $workshop]);
                                    })
                                @endphp
                                <x-input.select
                                    only-input
                                    without_label
                                    :elements="$workshops_to_assign"
                                    id="{{$role->name}}_workshop"
                                    name="workshop_id"
                                />
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
