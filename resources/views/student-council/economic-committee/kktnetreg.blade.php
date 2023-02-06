@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="{{ route('economic_committee') }}" class="breadcrumb" style="cursor: pointer">@lang('checkout.student-council-checkout')</a>
<a href="#!" class="breadcrumb">@lang('checkout.kktnetreg')</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('checkout.users_have_to_pay') ({{ \App\Models\Semester::current()->tag }}) </span>
                <table><tbody>
                    @foreach($users_not_payed as $user)
                      <tr><td>{{ $user->uniqueName }}</td></tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('checkout.payed_kktnetreg') ({{\App\Models\Semester::current()->tag}})</span>
                <table><tbody>
                    <tr>
                        <th colspan="2">@lang('print.user')</th>
                        <th>@lang('user.workshop')</th>
                        <th>@lang('checkout.amount')</th>
                    </tr>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->payer->name }}</td>
                            <td>{{ $transaction->comment }}</td>
                            <td>
                                @include('user.workshop_tags', ['user' => $transaction->payer])
                            </td>
                            <td>{{ $transaction->amount }}</td>
                        </tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
