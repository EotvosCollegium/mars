@can('administrate', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.income_expense')</span>
        <form method="POST" action="{{ route($route_base . '.transaction.add') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="comment" required text="checkout.description" />
                <x-input.text type="number" m=6 l=6 id="amount" required locale="checkout" />
                <x-input.select m=6 l=6 id="receiver" locale="checkout" :elements="\App\Models\User::collegists()" default="{{Auth::user()->id}}" />
                <x-input.select m=6 l=6 id="payer"   locale="checkout" :elements="\App\Models\User::collegists()" default="{{Auth::user()->id}}" />
                <x-input.checkbox id="in_checkout" checked text="checkout.add_transaction_descr"/>
            </div>
            <x-input.button floating class="btn=large right" icon="payments" />
        </form>
    </div>
</div>
@endcan
