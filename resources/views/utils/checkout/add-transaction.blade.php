@can('createTransaction', $checkout)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('checkout.add_transaction')</span>
        <blockquote>
            @lang('checkout.add_transaction_descr')
        </blockquote>
        <form method="POST" action="{{ route($route_base . '.transaction.add') }}">
            @csrf
            <div class="row">
                <x-input.radio m=6 l=6 name="type" value="EXPENSE" text="checkout.expense" />
                <x-input.radio m=6 l=6 name="type" value="INCOME" text="checkout.income" />
                @error('type')
                <div class="col s12"><span class="helper-text red-text">{{ $message }}</span></div>
                @enderror
                <x-input.text m=6 l=6 id="comment" required text="checkout.description" />
                <x-input.text type="number" m=6 l=6 id="amount" min="0" required locale="checkout" />
                <x-input.select m=6 l=6 id="receiver" locale="checkout" :elements="\App\Models\User::collegists()" default="{{Auth::user()->id}}" />
                <x-input.select m=6 l=6 id="payer"    locale="checkout" :elements="\App\Models\User::collegists()" default="{{Auth::user()->id}}" />
                @can('administrate', $checkout)
                <x-input.checkbox id="in_checkout" checked text="checkout.in_checkout_descr"/>
                @endcan
            </div>
            <x-input.button floating class="btn=large right" icon="payments" />
        </form>
    </div>
</div>
@endcan
