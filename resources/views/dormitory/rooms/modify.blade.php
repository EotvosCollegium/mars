@extends('layouts.app')
@section('title')
<a href="{{ route('rooms') }}" class="breadcrumb" style="cursor: pointer">@lang('rooms.rooms')</a>
<a href="#!" class="breadcrumb">@lang('rooms.edit')</a>
@endsection

@section('content')
@can('updateAny', \App\Models\Room::class) 
<div class="card">
    <div class="card-content">
    <form method="post" id="update-all" action="{{route('rooms.update')}}">
        @csrf
        @method('put')
        <div class="fixed-action-btn">
            <button type="submit" class="btn-floating btn-large" value="update-all">
                <i id="save_btn" class="large material-icons" value="update-all">save</i>
            </button>
        </div>
    </form>
    @foreach ($rooms as $room)
        <div class="row">
            @if ($room->capacity>1)
                <form method="post" id="update-remove" action="{{route('rooms.update-capacity', $room->name)}}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="type" value="remove"/>
                    <x-input.button s=3 class="right coli blue" icon="person_remove" value="update-remove"/>
                </form>
            @else
                <h5 class="col s3 center"></h5>
            @endif
            <h5 class="col s6 center">{{$room->name}}</h5>
            @if ($room->capacity<4)
                <form method="post" id="update-add" action="{{route('rooms.update-capacity', $room->name)}}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="type" value="add"/>
                    <x-input.button s=3 class="left coli blue" icon="person_add" value="update-add"/>
                </form>
            @endif
        </div>
        <div class="row">
            @php
                $width=floor(12/$room->capacity);
                $users_in_room=$room->users()->get()->pluck('id');
            @endphp
            @for ($i = 1; $i <= $room->capacity; $i++)
                @if ($users_in_room->count()>=$i)
                    <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$users" :default="$users_in_room[$i-1]" text="rooms.resident{{$i}}"/>
                @else
                    <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$users" text="rooms.resident{{$i}}"/>
                @endif
            @endfor
        </div>
    @endforeach
    </div>
</div>
@endcan
@endsection