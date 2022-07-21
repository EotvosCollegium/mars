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
        <nobr>{{ $rolegroup->first()->translatedName }}
            @if($rolegroup->first()->has_workshops || $rolegroup->first()->has_objects)
                :
            @endif
        </nobr>
    </span>

    {{-- objects --}}
    @foreach($rolegroup as $role)
        @if($rolegroup->first()->has_workshops || $rolegroup->first()->has_objects)
        <span class="new badge {{ $rolegroup->first()->color() }} tag" data-badge-caption="">
            <nobr>{{ $role->pivot->translatedName }}</nobr>
        </span>
        @endif
    @endforeach
    @if($newline ?? false) <br> @endif
@endforeach
