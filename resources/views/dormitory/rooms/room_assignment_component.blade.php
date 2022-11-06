<div>
    @foreach ($rooms as $room)
    <div class="row">
        @if ($room->capacity>1)
            <x-input.button s=3 class="right coli blue" icon="person_remove" wire:click="decrement_capacity($room)"/>
        @else
            <h5 class="col s3 center"></h5>
        @endif
        <h5 class="col s6 center">{{$room->name}}</h5>
        @if ($room->capacity<4)
            <x-input.button s=3 class="right coli blue" icon="person_add"  wire:click="increment_capacity($room)"/>
        @endif
    </div>
    <div class="row">
        @php
            $width=floor(12/$room->capacity);
            $users_in_room=$room->users()->get()->pluck('id');
        @endphp
        {{ var_dump($room) }}
        @for ($i = 1; $i <= $room->capacity; $i++)
            {{-- @if ($users_in_room->count()>=$i)
                <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$this->unassignedUsers"  text="rooms.resident{{$i}}"/>
            @else
                <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$this->unassignedUsers" text="rooms.resident{{$i}}"/>
            @endif --}}
        @endfor
    </div>
@endforeach
</div>
