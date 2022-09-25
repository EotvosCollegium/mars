@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="#!" class="breadcrumb">@lang('community-service.community-service')</a>
@endsection

@section('student_council_module') active @endsection

@section('content')

@foreach($semesters as $semester)
    @php
        // $transactions = $semester->transactions;
    @endphp
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $semester->tag }}</span>
                <div class="row">
                    <div class="col s12">
                        <table><tbody>
                            {{-- <tr><th colspan="3">@lang('checkout.incomes')</th></tr>
                            @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::kkt()])
                            @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::income()])

                            <tr><th colspan="3">@lang('checkout.expenses')</th></tr>
                            @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::expense()])
                            @include('utils.checkout.sum',  ['paymentType' => \App\Models\PaymentType::workshopExpense()])
                            <tr>
                                <th colspan="2">@lang('checkout.sum')</th>
                                <th class="right"><nobr>{{ number_format($semester->transactions->sum('amount'), 0, '.', ' ') }} Ft</nobr></th>
                            </tr> --}}
                        </tbody></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

@endsection