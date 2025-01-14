{{-- A table showing data of items in the 'items' array.
     Can be included from other views.
     --}}
<table>
    <thead>
        <tr>
            <th style="max-width: 30%;">@lang('reservations.item_status')</th>
            <th style="text-align: right;">@lang('reservations.item_name')</th>
            {{-- for fault report buttons --}}
            <th style="width: 250px;"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
        <tr>
            <td>
                @if($item->isOutOfOrder())
                <span class="new badge grey" data-badge-caption="">@lang('reservations.out_of_order')</span>
                @elseif($item->isFree())
                <span class="new badge green" data-badge-caption="">@lang('reservations.is_free')</span>
                @else
                <span class="new badge red" data-badge-caption="">@lang('reservations.is_occupied')</span>
                @endif
            </td>
            <td style="text-align: right;">
                <a href="{{ route('reservations.items.show', ['item' => $item]) }}">
                    {{ $item->name }}
                </a>
            </td>
            <td>
                {{-- The button triggers a modal dialog in which the user can provide an explanation about the problem. --}}

                {{-- modal trigger --}}
                <a @class([
                    'btn',
                    'waves-effect',
                    'waves-light',
                    'modal-trigger',
                    'right',
                    'coli' => !$item->out_of_order,
                    'blue' => !$item->out_of_order,
                ]) class="" href="#fault-reporting-modal-{{ $item->id }}">{{ __($item->out_of_order ? 'reservations.report_fix' : 'reservations.report_fault') }}</a>

                {{-- modal structure --}}
                <form method="POST"
                action="{{ route('reservations.items.report_fault', ['item' => $item]) }}"
                enctype='multipart/form-data'>
                    @csrf
                    <div id="fault-reporting-modal-{{ $item->id }}" class="modal">
                        <div class="modal-content">
                            <h4>{{ __($item->out_of_order ? 'reservations.report_fix' : 'reservations.report_fault') }}</h4>
                            <x-input.textarea id="fault-message-{{ $item->id }}"
                                      name="message"
                                      :text="__('general.description')"
                                      :helper="__('reservations.describe_what_happened')"/>
                        </div>
                        <div class="modal-footer">
                            <a href="#!" type="submit" class="waves-effect btn modal-close">@lang('general.cancel')</a>
                            <button class="waves-effect btn modal-close" type="submit">@lang('general.send')</button>
                        </div>
                    </div>
                </form>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@push('scripts')
<script>
// for the modal dialogs
$(document).ready(function(){
    $('.modal').modal();
});
</script>
@endpush
