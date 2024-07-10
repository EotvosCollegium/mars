@foreach ($workshops as $workshop)
    <span class="new badge {{ $workshop->color() }} scale-transition tag" id="user-workshop-{{ $user->id }}-{{ $workshop->id }}" data-badge-caption="">
        <nobr style="overflow: hidden;text-overflow: ellipsis;">{{$workshop->name}}</nobr>
    </span>
    @if($newline ?? false)
        <br id="br-user-workshop-{{ $user->id }}-{{ $workshop->id }}">
    @endif
@endforeach
