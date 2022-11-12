<tr>
    <th colspan="5">@lang('checkout.sum')</th>
    <th class="right-align"><nobr>{{ number_format($semester->transactions->sum('amount'), 0, '.', ' ') }} Ft</nobr></th>
</tr>
