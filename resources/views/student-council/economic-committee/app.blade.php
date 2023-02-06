@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="#!" class="breadcrumb">@lang('checkout.student-council-checkout')</a>
@endsection
@section('student_council_module') active @endsection

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
                    Tedd be (ha pozitív) / vedd ki (ha negatív) ezt az összeget a kasszából: <b class="coli-text text-orange">{{ number_format($transactions_not_in_checkout, 0, '.', ' ')}} Ft</b>
                    <br>
                    Figyelem: ebben az összegben a még általad (zsebből) ki nem fizetett vásárlások is benne vannak,
                    így ha kiveszed az összeget, attól a rendszerben lévő tartozásokat még ki kell elégítened!
                    <br>
                    Miután kezelted az összeget, kattints a lenti zöld gombra!
                    @endif
                </blockquote>
                @if($transactions_not_in_checkout != 0)
                    <form method="POST" action="{{ route($route_base . '.to_checkout') }}">
                        @csrf
                        <x-input.button floating class="btn-large right green" icon="payments"/>
                    </form>
                @endif
                @endcan
            </div>
        </div>
        <div class="row">
            @can('addKKTNetreg', \App\Models\Checkout::class)
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <a href="{{ route('kktnetreg') }}" class="btn waves-effect right">
                            KKT/NetReg fizetők listája</a>
                        <span class="card-title">@lang('checkout.pay_kktnetreg')</span>
                        <form method="POST" action="{{ route('kktnetreg.pay') }}">
                            @csrf
                            <div class="row">
                                <div class="col s12">
                                    <blockquote>@lang('checkout.pay_kkt_descr')<br>A Netreg tranzakcióid a <a href="{{route('admin.checkout')}}"> rendszergazdai kasszában</a> láthatod.</blockquote>
                                    <x-input.select l=4 :elements="$users_not_payed" id="user_id" text="general.user" :formatter="function($user) { return $user->uniqueName; }" />
                                    <x-input.text  m=6 l=4 id="kkt" text="KKT" type="number" required min="0" :value="config('custom.kkt')" />
                                    <x-input.text  m=6 l=4 id="netreg" text="NetReg" type="number" required min="0" :value="config('custom.netreg')" />
                                </div>
                            </div>
                            <x-input.button floating class="btn-large right" icon="send" />
                        </form>
                    </div>
                </div>
            </div>
            @endcan
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
    @php
        $semesters = $semesters->load('workshopBalances.workshop');
    @endphp
    @foreach($semesters as $semester)
    @php
        $transactions = $semester->transactions;
    @endphp
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $semester->tag }}</span>
                <div class="row">
                    <div class="col s12">
                        <table><tbody>
                            @include('utils.checkout.header')
                            <tr><th>@lang('checkout.incomes')</th></tr>
                            @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::kkt()])
                            @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::income()])
                            <tr><th>@lang('checkout.expenses')</th></tr>
                            @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::expense()])
                            @include('utils.checkout.sum',  ['paymentType' => \App\Models\PaymentType::workshopExpense()])
                            @include('utils.checkout.footer')
                        </tbody></table>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <table class="highlight responsive-table centered" style="display: block;overflow-x:auto;">
                            <thead>
                                <tr>
                                    <th class="valign-wrapper">@lang('checkout.workshop_balances')</th>
                                    <th>@lang('general.members')@if($semester->isCurrent())*@endif</th>
                                    <th>
                                        @lang('checkout.allocated_balance')
                                        @if($semester->isCurrent())
                                        <br>
                                            <x-input.button :href="route('economic_committee.workshop_balance')" floating class="btn-small grey" icon="refresh" />
                                        @endif
                                    </th>
                                    <th>@lang('checkout.used_balance')</th>
                                    <th>@lang('checkout.remaining_balance')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($semester->workshopBalances as $workshop_balance)
                                <tr>
                                    <td>{{ $workshop_balance->workshop->name }} </td>
                                    <td>{{ $workshop_balance->resident . ' - ' . $workshop_balance->extern . ' (+' . $workshop_balance->not_yet_paid . ')' }}</td>
                                    <td>{{ $workshop_balance->allocated_balance }}</td>
                                    <td>{{ $workshop_balance->used_balance }}
                                        @can('administrate', \App\Models\Checkout::studentsCouncil())
                                            <form action="{{ route('economic_committee.workshop_balance.update', ['workshop_balance' => $workshop_balance]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <x-input.text type="number" id="amount" only-input withoutLabel/>
                                                <x-input.button type="submit" class="btn waves-effect" text="Kifizetés" />
                                            </form>
                                        @endcan
                                    </td>
                                    <td>{{ $workshop_balance->allocated_balance - $workshop_balance->used_balance }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($semester->isCurrent())
                        <blockquote>*@lang('checkout.workshop_balance_descr', [
                            'kkt' => config('custom.kkt'),
                            'resident' => config('custom.workshop_balance_resident'),
                            'extern' => config('custom.workshop_balance_extern')
                        ])</blockquote>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
