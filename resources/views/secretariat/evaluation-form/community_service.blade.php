
<div class="row">
    <div class="col">
        <p>Collegiumi tisztségek:
        @include('user.role_tags', ['roles' => $position_roles])</p>
        @if($position_roles->count() == 0) Nincs @endif
    </div>
</div>
<blockquote>
    A fenti listában meg kell jelennie a következő tisztségeknek: műhelytitkár, választmányi tag, bizottsági tag, CHÖK titkár, kuratóriumi diáktag, rendszergazda, etikai biztos.<br>
    Ha bármelyiket betöltöd, de nem jelenik meg, akkor jelezd azt a CHÖK titkárnak (műhelytitkár esetén a tudományos alelnöknek).
</blockquote>
Collegiumhoz kötődő rendezvényszervezés, vagy egyéb, a Collegiumhoz kötődő közéleti tevékenység:
<table>
    <tbody>
        @forelse ($community_services as $service)
        <tr>
            <td>{{ $service->description }}</td>
            <td>{{ $service->created_at->format('Y. m. d.') }}</td>
            <td><span class="new badge {{ $service->getStatusColor() }}" data-badge-caption="">
                {{ $service->status }}
                ({{ $service->approver->name }})
                </span>
            </td>
        </tr>
        @empty
        <tr>
            <td>
                Nem töltöttél fel közösségi tevékenységet a félév során.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<h6 style="margin-top:20px">Új közösségi tevékenység hozzáadása</h6>
@include('student-council.community-service.request')

