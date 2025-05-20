@php
    /**
     * Variables: 
     *  $user -> the current user
     *  $users -> all users, used to transfer money between print accounts
     *  $printer_configurations -> the model corresponding to the printer_configuration, which is used to print (currently only one is available at all times)
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
            </p>
            <p>
            @lang('print.available_money'): <b class="coli-text text-orange"> {{ $printAccount->balance }}</b> HUF.
            @lang('print.upload_money')
            </p>
            @if($printAccount->availableFreePrintingCredits()->sum('amount') > 0)
            <p>
            @lang("print.available_free_printing_credits", ['number_of_free_printing_credits' => $printAccount->availableFreePrintingCredits()->sum('amount')])
            </p>
            <p>
            @lang('print.payment_methods_cannot_be_mixed')
            </p>
            @endif
            <p>
            @lang('print.double_sided_pricing')
            </p>
            <p>@lang('print.no-paper-description')</p>
        </blockquote>
        @foreach($printer_configurations as $printer_configuration)
            <form id="{{ 'printer_edit_form_'.$printer_configuration->id}}" method="POST" action="{{ route('print.update', ['printer_configuration' => $printer_configuration]) }}">
                @method('PUT')
                @csrf
            </form>
        @endforeach
        <form method="POST" action="{{ route('print.print-job.store') }}" enctype="multipart/form-data">
      
        @csrf
            <table>
                <tr>
                    <th>@lang('print.printer_configuration_selection')</th>
                    @can('viewLpFlags', App\Models\PrinterConfiguration::class)
                        <th>lp flags</th>
                    @endcan
                    <th>@lang('print.one_sided_cost')</th>
                    <th>@lang('print.two_sided_cost')</th>
                    <th>@lang('print.no_paper')</th>
                    @can('manageAnyActivation', App\Models\PrinterConfiguration::class)
                        <th>Manage activation</th>
                    @endcan
                </tr>
                @foreach($printer_configurations as $index => $printer_configuration)
                @can('use', $printer_configuration)
                <tr>
                    <td>
                        <label>
                            <input type="radio" name="printer_configuration" value="{{ $printer_configuration->id }}"
                                {{ $index === 0 ? 'checked' : '' }} {-- Default selection --}
                            >
                            <span class="black-text">{{$printer_configuration->description}}
                            @can('manageActivation', $printer_configuration)
                            @if(!$printer_configuration->active)
                            &ndash; <span class="blue-text" style="font-weight: bold;">inactive</span>
                            @endif
                            @endcan
                            </span>
                        </label>
                    </td>
                    @can('viewLpFlags', App\Models\PrinterConfiguration::class)
                        <td> {{$printer_configuration->lp_flags}}</td>
                    @endcan
                    <td> {{$printer_configuration->one_sided_cost}}</td>
                    <td>
                        @if(isset($printer_configuration->two_sided_cost))
                            {{ $printer_configuration->two_sided_cost }}
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td>
                        @can('reportError', $printer_configuration)
                            <x-input.button type="submit" form="{{ 'printer_edit_form_'.$printer_configuration->id}}" name="no_paper" class="coli blue" text="print.no_paper" :onlyInput=true />
                        @endcan
                    </td>
                    @can('manageActivation', $printer_configuration)
                    <td>
                        @if($printer_configuration->active)
                            <x-input.button type="submit" form="{{ 'printer_edit_form_'.$printer_configuration->id}}" name="deactivate" class="red" text="Deactivate" :onlyInput=true />
                        @else
                            <x-input.button type="submit" form="{{ 'printer_edit_form_'.$printer_configuration->id}}" name="activate" class="green" text="Activate" :onlyInput=true />
                        @endif
                    </td>
                    @endcan
                @endcan
                </tr>
            @endforeach
            </table>
            
            <div class="row">
                @if($printAccount->availableFreePrintingCredits()->sum('amount') > 0) {{-- only show if the user has active free pages --}}
                    <x-input.checkbox s=12 name="use_free_printing_credits" text="print.use_free_printing_credits" 
                        checked="{{ session()->get('use_free_printing_credits') ? 'checked' : '' }}"
                    />
                @endif
                <x-input.text s=4 m=2 id="copies" type="number" min="1" max="99" :value="1" required text="print.number_of_copies"/>
                <x-input.file s=8 id="file" accept=".pdf" required text="print.select_document"/>
                <x-input.button s=4 m=2 class="right" text="print.print"/>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const radios = document.querySelectorAll('input[name="printer_configuration"]');

    const savedPrinter = localStorage.getItem("selectedPrinter");

    if (savedPrinter) {
        const selectedRadio = document.querySelector(`input[name="printer_configuration"][value="${savedPrinter}"]`);
        if (selectedRadio) {
            selectedRadio.checked = true;
        }
    }

    radios.forEach(function (radio) {
        radio.addEventListener("change", function () {
            localStorage.setItem("selectedPrinter", this.value);
        });
    });
});
</script>