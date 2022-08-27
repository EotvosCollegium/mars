<table>
    <tbody>
        @foreach ($user->roles->sortBy('name') as $role)
        <tr>
            <td>@lang('role.'.$role->name)</td>
            <td>
                {{ $role->pivot->translatedName }}
            </td>
            <td>
                @can('updatePermission', [$user, $role, $role->pivot->workshop ?? $role->pivot->object])
                <form action="{{ route('users.roles.delete', ['user' => $user->id, 'role' => $role->id]) }}" method="post">
                @csrf
                    @method('delete')
                    <input type="hidden" name="object" value="{{$role->pivot->workshop_id ?? $role->pivot->object_id}}">
                    <x-input.button floating class="right red" icon="delete" />
                </form>
                @endcan
            </td>
        </tr>
        @endforeach
        @can('updateAnyPermission', $user)
        <tr><td></td></tr>
        @foreach (App\Models\Role::all()->sortBy('name') as $role)
            @can('updateAnyPermission', [$user, $role])
            @if(!$user->roles->contains($role) || $role->has_objects || $role->has_workshops)
                <form action="{{ route('users.roles.add', ['user' => $user->id, 'role'=>$role->id]) }}" method="post">
                    @csrf
                    <tr>
                        <td>@lang('role.'.$role->name)</td>
                        <td>
                            @if($role->has_objects)
                                <x-input.select only-input without_label :elements="$role->objects" :formatter="function($o) { return $o->translatedName; }" id="{{$role->name}}_object" name="object_id"/>
                            @elseif($role->has_workshops)
                                <x-input.select only-input without_label :elements="\App\Models\Workshop::all()" id="{{$role->name}}_workshop" name="workshop_id"/>
                          @endif
                        </td>
                        <td>
                            <x-input.button floating class="right green" icon="add" />
                        </td>
                    </tr>
                </form>
            @endif
            @endcan
        @endforeach
        @endcan
    </tbody>
</table>
