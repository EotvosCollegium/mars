@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="#!" class="breadcrumb">@lang('admin.checkout')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('checkout.checkout')</span>
                <blockquote>
                    @lang('checkout.current_balance'):
                    <b class="coli-text text-orange"> {{ number_format($current_balance, 0, '.', ' ') }} Ft</b>.<br>
                </blockquote>
                @can('administrate', $checkout)
                <blockquote>    
                    @lang('checkout.current_balance_in_checkout'):
                    <b class="coli-text text-orange"> {{ number_format($current_balance_in_checkout, 0, '.', ' ') }} Ft</b>.<br>
                    @if($transactions_not_in_checkout != 0)
                    @lang('checkout.to_checkout'): <b>{{$transactions_not_in_checkout}} Ft</b>
                    @endif
                </blockquote>
                @if($transactions_not_in_checkout != 0)
                    <form method="POST" action="{{ route($route_base . '.to_checkout') }}">
                        @csrf
                        <x-input.button floating class="right green" icon="payments"/>
                    </form>
                @endif
                @endcan
            </div>
        </div>
        <div class="row">
            <div class="col s12">
                @include('utils.checkout.add-transaction')
            </div>
            <div class="col s12">
                @include('utils.checkout.depts')
            </div>
            <div class="col s12">
                @include('utils.checkout.my_transactions')
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        @foreach ($semesters as $semester)
        @php
            $transactions = $semester->transactions;
        @endphp
            <div class="card">
                <div class="card-content">
                    <span class="card-title">{{ $semester->tag }}</span>
                    <div class="row">
                        <div class="col s12">
                            <table><tbody>
                                <tr>
                                    <th>@lang('checkout.incomes')</th>
                                    @can('administrate', $checkout)
                                    <th>@lang('checkout.payer')</th>
                                    <th>@lang('checkout.receiver')</th>
                                    <th>Fizetve</th>
                                    <th>Kasszába került</th>
                                    @endcan
                                    <th>Dátum</th>
                                    <th>Összeg</th>
                                </tr>
                                @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::print()])
                                @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::netreg()])
                                @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::income()])
                                <tr><th colspan="3">@lang('checkout.expenses')</th></tr>
                                @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::expense()])
                                <tr>
                                    <th colspan="6">@lang('checkout.sum')</th>
                                    <th class="right"><nobr>{{ number_format($semester->transactions->sum('amount'), 0, '.', ' ') }} Ft</nobr></th>
                                </tr>
                            </tbody></table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
