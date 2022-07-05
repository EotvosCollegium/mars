{{-- Input: $roles, $newline = false --}}
{{-- Show roles in badges. Roles with same base will be grouped. --}}
@php
$chunks = $roles->chunkWhile(function($current, $key, $chunk) {
    return $current->name === $chunk->last()->name;
});
@endphp

@foreach($chunks as $rolegroup)
    {{-- base role --}}
    <span class="new badge {{ $rolegroup->first()->color() }} tag" data-badge-caption="">
        <nobr>{{ $rolegroup->first()->name() }}
            @if($rolegroup->first()->pivot->workshop_id || $rolegroup->first()->pivot->object_id)
                :
            @endif
        </nobr>
    </span>

    {{-- objects --}}
    @foreach($rolegroup as $role)
        @if($role->pivot->object_id)
        <span class="new badge {{ $rolegroup->first()->color() }} tag" data-badge-caption="">
            <nobr>@lang('role.'.$role->pivot->object->name)</nobr>
        </span>
        @endif
        @if($role->pivot->workshop_id)
            <span class="new badge {{ $rolegroup->first()->color() }} tag" data-badge-caption="">
            <nobr>{{$role->pivot->workshop->name }}</nobr>
        </span>
        @endif
    @endforeach
    @if($newline ?? false) <br> @endif
@endforeach
