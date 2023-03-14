@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="#!" class="breadcrumb">Rendszergazdai kassza</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        @include('utils.checkout.status')
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
