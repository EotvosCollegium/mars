@php
    /**
     * Variables: 
     *  $user -> the current user
     *  $users -> all users, used to transfer money between print accounts
     *  $printer -> the model corresponding to the printer, which is used to print (currently only one is available at all times)
    */
    $printAccount = $user->printAccount;
@endphp
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('print.print_document')</span>
        <blockquote>
            <p>
            @lang('print.pdf_description')
            @lang("print.pdf_maxsize", ['maxsize' => config('print.pdf_size_limit')/1000])
            @lang('print.costs',['one_sided' => config('print.one_sided_cost'), "two_sided" => config('print.two_sided_cost')])
            </p><p>
            @lang('print.available_money'): <b class="coli-text text-orange"> {{ $printAccount->balance }}</b> HUF.
            @lang('print.upload_money')
            </p>
            <p>
            @lang('print.payment_methods_cannot_be_mixed')
            </p>
        </blockquote>
        <form class="form-horizontal" role="form" method="POST" action="{{ route('print.print-job.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <x-input.file l=8 xl=10 id="file" accept=".pdf" required text="print.select_document"/>
                <x-input.text l=4 xl=2  id="copies" type="number" min="1" :value="1" required text="print.number_of_copies"/>
                <x-input.checkbox s=8 xl=4 name="two_sided" checked text="print.twosided"/>
                @if($printAccount->availableFreePages()->sum('amount') > 0) {{-- only show if the user has active free pages --}}
                    <x-input.checkbox s=8 xl=4 name="use_free_pages" text="print.use_free_pages" 
                        checked="{{ session()->get('use_free_pages') ? 'checked' : '' }}"
                    />
                    <x-input.button s=4 class="right" text="print.print"/>
                @else
                    <x-input.button s=4 xl=8 class="right" text="print.print"/>
                @endif
            </div>
        </form>
        <div class="row">
            <div class="col l9">
                <blockquote>
                    @if($printer->paper_out_at != null) 
                        @lang('print.no-paper-reported', ['date' => $printer->paper_out_at])
                    @else
                        @lang('print.no-paper-description')
                    @endif
                </blockquote>
            </div>
            @if($printer->paper_out_at != null && $user->can('handleAny', \App\Models\PrintAccount::class))
                <form method="POST" action="{{ route('print.update', ['printer' => $printer->id]) }}">
                    @method('PUT')
                    @csrf
                    <x-input.button l=3 class="right coli blue" text="Papír újratöltve"/>
                    {{-- value set to "1" instead of true so that laravel has no problem with validation --}}
                    <input name="no_paper" value="0" type="hidden" />
                </form>
            @else
                <form method="POST" action="{{ route('print.update', ['printer' => $printer->id]) }}">
                    @method('PUT')
                    @csrf
                    <x-input.button l=3 class="right coli blue" text="print.no_paper" />
                    <input name="no_paper" value="1" type="hidden" />
                </form>
            @endif
        </div>
    </div>
</div>
