@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="#!" class="breadcrumb">Rendszergazdai kassza</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Kassza</span>
                <blockquote>
                    Jelenlegi összeg:
                    <b class="coli-text text-orange"> {{ number_format($current_balance, 0, '.', ' ') }} Ft</b>.<br>
                </blockquote>
                @can('administrate', $checkout)
                <blockquote>
                    Jelenlegi összeg a kasszában:
                    <b class="coli-text text-orange"> {{ number_format($current_balance_in_checkout, 0, '.', ' ') }} Ft</b>.<br>
                    @if($transactions_not_in_checkout != 0)
                    Tedd be (ha pozitív) / vedd ki (ha negatív) ezt az összeget a kasszából: <b>{{$transactions_not_in_checkout}} Ft</b>, majd kattints a zöld gombra!
                    <br>
                    Figyelem: ebben az összegben a még általad (zsebből) ki nem fizetett vásárlások is benne vannak,
                    így ha kiveszed az összeget, attól a rendszerben lévő tartozásokat még ki kell elégítened!
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
                                @include('utils.checkout.header')
                                <tr><th>Bevétel</th></tr>
                                @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::print()])
                                @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::netreg()])
                                @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::income()])
                                <tr><th>Kiadás</th></tr>
                                @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::expense()])
                                @include('utils.checkout.footer')
                            </tbody></table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
