<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('print.transfer_money')</span>
        <blockquote>
        @lang('print.how_transfer_works')
        </blockquote>
        <form method="POST" action="{{ route('print.print-account.update') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <x-input.select l=5 id="other_user" :formatter="fn($user) => $user->uniqueName" :elements="$users" text="print.user"/>
                <x-input.text l=5 id="amount" type="number" min="1" required text="print.amount"/>
                <input type="hidden" name="user" value="{{ user()->id }}" />
                <x-input.button l=2 class="right" text="print.send"/>
            </div>
        </form>
    </div>
</div>
