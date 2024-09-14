@can('administrate', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">Bevétel hozzáadása</span>
        <blockquote>
            Ezzel még a tranzakció nem kerül a kasszába (lásd fenn). Bevételt csak a kasszafelelős rögzíthet - azaz Te ;)
        </blockquote>
        <form method="POST" action="{{ route($route_base . '.income.add') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="income-comment" name="comment" required text="Megjegyzés" />
                <x-input.text type="number" m=6 l=6 id="income-amount" name="amount" min=0 required text="Összeg" />
            </div>
            <x-input.button floating class="btn-large right" icon="payments" />
        </form>
    </div>
</div>
@endcan
