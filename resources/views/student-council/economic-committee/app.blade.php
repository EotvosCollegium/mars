@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="#!" class="breadcrumb">Választmányi kassza</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        @include('utils.checkout.status')
        <div class="row">
            @can('addKKTNetreg', \App\Models\Checkout::class)
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <a href="{{ route('kktnetreg') }}" class="btn waves-effect right">
                            KKT/NetReg fizetők listája</a>
                        <span class="card-title">KKT/Netreg fizetése</span>
                        <form method="POST" action="{{ route('kktnetreg.pay') }}">
                            @csrf
                            <div class="row">
                                <div class="col s12">
                                    <blockquote>
                                        Ha valaki fizetni szeretne neked, azt írd fel itt. Csak aktív státuszú collegisták választhatóak ki, akik még nem fizettek KKT-t/Netreget. <br>
                                        A tranzakcióról emailben értesítést kapnak, és az internet-elérésük automatikusan meghosszabbításra kerül.<br>
                                        A Netreg tranzakcióid a <a href="{{route('admin.checkout')}}"> rendszergazdai kasszában</a> láthatod.
                                    </blockquote>
                                    @can('administrate', $checkout)
                                    <blockquote>A gazdasági alelnök, a kulturális bizottság tagjai és a rendszergazdák szedhetnek be KKT-t/Netreget. Ezeket a tranzakciókat a tartozások alatt találod.</blockquote>
                                    @endcan
                                    <x-input.select l=4 :elements="$users_not_paid" id="user_id" text="general.user" :formatter="function($user) { return $user->uniqueName; }" />
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
                @include('utils.checkout.add-income')
            </div>
            <div class="col s12">
                @include('utils.checkout.add-expense')
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
                            <tr><th colspan="7">Bevétel</th></tr>
                            @include('utils.checkout.sum', ['paymentType' => \App\Models\PaymentType::kkt()])
                            @include('utils.checkout.list', ['paymentType' => \App\Models\PaymentType::income()])
                            <tr><th colspan="7">Kiadás</th></tr>
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
                                    <th class="valign-wrapper">Műhelyek egyenlegei</th>
                                    <th>Tagok @if($semester->isCurrent())*@endif </th>
                                    <th>
                                        Kiosztott egyenleg
                                    </th>
                                    <th>Felhasznált egyenleg @if($semester->isCurrent())@can('administrate', $checkout) ** @endcan @endif</th>
                                    <th>Fennmaradó összeg</th>
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
                        <blockquote>
                            *Bentlakók - bejárók (+ akik még nem fizettek, de aktív státuszú collegisták)<br>
                            Azok közül, akik fizettek, minden bentlakó után a műhely {{config('custom.kkt')}} * {{config('custom.workshop_balance_resident')}}, minden bejáró után {{config('custom.kkt')}} * {{config('custom.workshop_balance_extern')}} forintot kap.
                            (Ha egy collegistának több műhelye is van, a műhelyei megosztoznak az összegen.)
                        </blockquote>
                        @can('calculateWorkshopBalance', \App\Models\Checkout::class)
                        <blockquote>
                            A félévben az adott műhelynek szánt összeg számolását a gazdasági alelnök (illetve a rendszergazdák) tudják elindítani. <br>
                            Ez növelheti, és csökkentheti is az érintett műhelynek kiosztott összeget.
                            Célszerű a KKT-k befizetését és adminisztrálását követően lefuttatni, és utána többször nem elindítani, mivel az egyes műhelyekből távozók és érkezők befolyásolnák ezt a számot. <br>
                            <x-input.button :href="route('economic_committee.workshop_balance')" class="btn-small red" text="Műhelyeknek járó összeg számolása" />
                        </blockquote>
                        @endcan
                        @can('administrate', $checkout)
                        <blockquote>
                            **A beviteli mezőbe pozitív összeggel írd be a kiadást, negatívval az előző félévről megmaradt egyenleget.
                        </blockquote>
                        @endcan
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
