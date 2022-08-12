@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="{{ route('secretariat.user.list') }}" class="breadcrumb" style="cursor: pointer">@lang('admin.user_management')</a>
<a href="{{ route('secretariat.permissions.list') }}" class="breadcrumb" style="cursor: pointer">@lang('admin.permissions')</a>
<a href="#!" class="breadcrumb">{{ $user->name }}</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')
<div class="row">
    <div class="col s12">
    {{-- Roles of user --}}
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $user->name }}@lang('admin.users_roles')</span>
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
                                <form action="{{ route('secretariat.permissions.remove', ['user' => $user->id, 'role' => $role->id]) }}" method="post">
                                @csrf
                                    <input type="hidden" name="object" value="{{$role->pivot->workshop_id ?? $role->pivot->object_id}}">
                                    <x-input.button floating class="right red" icon="delete" />
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Rest of the roles --}}
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('admin.other_permissions') </span>
                <div class="row">
                @foreach (App\Models\Role::all()->sortBy('name') as $role)
                    @can('updateAnyPermission', [$user, $role])
                    @if(!$user->roles->contains($role) || $role->has_objects || $role->has_workshops)
                        <form action="{{ route('secretariat.permissions.edit', ['user' => $user->id, 'role'=>$role->id]) }}" method="post">
                            @csrf
                            @if($role->has_objects)
                                <x-input.select s=11 without_label :elements="$role->objects" :formatter="function($o) { return $o->translatedName; }" id="{{$role->name}}_object" name="object_id" :placeholder="__('role.'.$role->name)"/>
                            @elseif($role->has_workshops)
                                <x-input.select s=11 without_label :elements="\App\Models\Workshop::all()" id="{{$role->name}}_workshop" name="workshop_id" :placeholder="__('role.'.$role->name)"/>
                            @else
                                <x-input.text s=11 without_label id="blank" :value="__('role.'.$role->name)" disabled/>
                            @endif
                            <div class="input-field col s1"><x-input.button floating class="right green" icon="add" /></div>
                        </form>
                    @endif
                        @endcan
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
