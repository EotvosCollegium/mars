@can('createTransaction', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.add_expense')</span>
        <blockquote>
            @can('administrate', $checkout)
            Ha költöttél valamire / kifizetsz valamit valakinek, itt rögzítsd. 
            @else
            Ha fizettél valamit, itt rögzítsd. A kasszafelelős majd megtéríti az összeget.
            @endcan
        </blockquote>
        <form method="POST" action="{{ route($route_base . '.transaction.add') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="comment" required text="checkout.description" />
                <x-input.text type="number" m=6 l=6 id="amount" min="0" required locale="checkout" />
                @can('administrate', $checkout)
                <x-input.select m=6 l=6 id="payer" locale="checkout" :elements="\App\Models\User::collegists()" default="{{Auth::user()->id}}" :formatter="function($user) { return $user->uniqueName; }" />
                <x-input.checkbox m=6 l=6 id="in_checkout" checked text="checkout.in_checkout_descr"/>
                @endcan
            </div>
            <x-input.button floating class="btn-large right" icon="payments" />
        </form>
    </div>
</div>
@endcan
