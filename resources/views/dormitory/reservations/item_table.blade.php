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
                <form method="POST"
                action="{{ route('reservations.items.report_fault', ['item' => $item]) }}"
                enctype='multipart/form-data'>
                    @csrf
                    <x-input.button @class([
                        'right',
                        'coli' => !$item->out_of_order,
                        'blue' => !$item->out_of_order,
                    ]) :text="__($item->out_of_order ? 'reservations.report_fix' : 'reservations.report_fault')" />
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
