<div class="card">
    <div class="card-content">
        <span class="card-title">Kassza</span>
        <blockquote>
            Jelenlegi összeg:
            <b class="coli-text text-orange"> {{ number_format($current_balance, 0, '.', ' ') }} Ft</b>.<br>
        </blockquote>
        @can('administrate', $checkout)
        <blockquote>
            Jelenlegi összeg a kasszában:
            <b class="coli-text text-orange"> {{ number_format($current_balance_in_checkout, 0, '.', ' ') }} Ft</b>.<br>
            @if($transactions_not_in_checkout != 0)
            Tedd be (ha pozitív) / vedd ki (ha negatív) ezt az összeget a kasszából: <b class="coli-text text-orange">{{ number_format($transactions_not_in_checkout, 0, '.', ' ')}} Ft</b>, majd kattints a zöld gombra!
            <br>
            Figyelem: ebben az összegben a még általad (zsebből) ki nem fizetett vásárlások is benne vannak,
            így ha kiveszed az összeget, attól a rendszerben lévő tartozásokat még ki kell elégítened!
            @endif
        </blockquote>
        @if($transactions_not_in_checkout != 0)
            <form method="POST" action="{{ route($route_base . '.to_checkout') }}">
                @csrf
                <x-input.button floating class="btn-large right green" icon="payments"/>
            </form>
        @endif
        @endcan
        Kasszafelelős: <i>{{ $checkout->handler?->name }}</i>
    </div>
</div>