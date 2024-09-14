@can('createTransaction', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">Kiadás hozzáadása</span>
        <blockquote>
            @can('administrate', $checkout)
            Ha költöttél valamire vagy kifizettél valamit valakinek, akkor azt itt rögzítsd.
            A lenti fizető mezőnél válaszd ki azt a személyt (adott esetben magadat), aki a vásárlást végezte.<br>
            A jobb oldali mezőt akkor pipáld ki, hogyha a vásárlást kifizetted a vásárló személynek.
            Fontos: a vásárlás NEM kerül a kasszába akkor sem, hogyha bepipálod a mezőt, a rendszer úgy veszi,
            hogy a kifizetés zsebből történt.<br>
            Amennyiben a mezőt nem pipálod be, akkor csak a vásárlás tényét rögzítetted a rendszerben,
            ám a kifizetés még nem történt meg, így a rendszerben a vásárló személynek még tartozni fogsz.
            @else
            Ha vettél valamit, itt rögzítsd. A kasszafelelős majd megtéríti az összeget.
            @endcan
        </blockquote>
        @if($checkout->name == \App\Models\Checkout::STUDENTS_COUNCIL)
        <blockquote>
            @can('administrate', $checkout)
            A műhelykiadásokat a műhelyek egyenlegeinél, a felhasznált egyenleg módosításánál rögzítsd.
            @else
            A műhelykiadásokat csak a kasszafelelős tudja közvetlenül kezelni, így azt ne itt rögzítsd.
            @endcan
        </blockquote>
        @endif
        <form method="POST" action="{{ route($route_base . '.expense.add') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="expense-comment" name="comment" required text="Megjegyzés" />
                <x-input.text type="number" m=6 l=6 id="expense-amount" name="amount" min=0 required text="Összeg" />
                @can('administrate', $checkout)
                <x-input.select m=6 l=6 id="expense-payer" name="payer" text="Fizető" :elements="\App\Models\User::collegists()" default="{{user()->id}}" :formatter="function($user) { return $user->uniqueName; }" />
                <x-input.checkbox m=6 l=6 id="expense-paid" name="paid" checked text="A tartozás kifizetésre került"/>
                @endcan
                <x-input.file s="12" id="expense-receipt" name="receipt" style="margin-top:auto" accept=".pdf,.jpg,.png,.jpeg,.gif"
                                  text="checkout.receipt" required/>
            </div>
            <x-input.button floating class="btn-large right" icon="payments" />
        </form>
    </div>
</div>
@endcan
