@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="{{ route('economic_committee') }}" class="breadcrumb" style="cursor: pointer">Választmányi kassza</a>
<a href="#!" class="breadcrumb">KKT/Netreg</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Még nem fizettek ({{ \App\Models\Semester::current()->tag }}) </span>
                <table><tbody>
                    @foreach($users_not_paid as $user)
                      <tr><td>{{ $user->uniqueName }}</td></tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Fizettek ({{\App\Models\Semester::current()->tag}})</span>
                <table><tbody>
                    <tr>
                        <th colspan="2">@lang('general.user')</th>
                        <th>@lang('user.workshop')</th>
                        <th>Összeg</th>
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
