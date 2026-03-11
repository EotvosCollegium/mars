<div class="card">
    <div class="card-content">
        <span class="card-title">KKT kezelése</span>
        <blockquote>
            <p>A KKT szempontjából bentlakónak az számít, aki szerepel a <a href="{{ route('rooms') }}">szobabeosztásban</a> (így BB-sek és collegista vendégek is). Ne felejtsd el a szobabeosztást aktualizálni!</p>
            <p>A gazdasági alelnök, rendszergazdák és "@lang('role.kkt-handler')" jogosultsággal rendelkező felhasználók szedhetnek be KKT-t/Netreget.</p>
            <p>A műhelyeknek allokált összeg a félévhez tartozó "műhelyeknek járó összeg számolása" gomb megnyomásakor kerül kiszámításra. Ha egy collegista több műhelynek is a tagja, a műhelyei megosztoznak az összegen.</p>
        </blockquote>
        <form method="POST" action="{{ route('kktnetreg.configure') }}">
            @csrf
            <div class="row hide-on-small-only">
                <div class="col m3"></div>
                <div class="col m3">
                    <h6>Fizetendő összeg</h6>
                </div>
                <div class="col m3">
                    <h6>Műhelykeretbe allokált</h6>
                </div>
                <div class="col m3">
                    <h6>Maradék</h6>
                </div>
            </div>
            <div class="row valign-wrapper" style="flex-wrap: wrap">
                <div class="col s12 m3">
                    <h6>Bentlakó*</h6>
                </div>

                <x-input.text type="number" s=12 m=3 id="total_kkt_resident" :value=$total_kkt_resident min=0 required text="Fizetendő összeg" />

                <x-input.text type="number" s=12 m=3 id="workshop_balance_resident" :value=$workshop_balance_resident min=0 required text="Műhelykeretbe allokált" />

                <x-input.text type="number" s=12 m=3 id="remainder_resident" disabled required text="Maradék" />
            </div>
            <div class="row valign-wrapper" style="flex-wrap: wrap">
                <div class="col s12 m3">
                    <h6>Bejáró</h6>
                </div>

                <x-input.text type="number" s=12 m=3 id="total_kkt_extern" :value=$total_kkt_extern min=0 required text="Fizetendő összeg" />

                <x-input.text type="number" s=12 m=3 id="workshop_balance_extern" :value=$workshop_balance_extern min=0 required text="Műhelykeretbe allokált" />

                <x-input.text type="number" s=12 m=3 id="remainder_extern" disabled required text="Maradék" />
            </div>
            <x-input.button floating class="btn-large right" icon="save" />
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function calculateRemainder(totalId, workshopId, remainderId) {
            const total = parseFloat(document.getElementById(totalId).value) || 0;
            const workshop = parseFloat(document.getElementById(workshopId).value) || 0;
            const remainder = total - workshop;
            document.getElementById(remainderId).value = remainder;
            if (remainder < 0) {
                document.getElementById(remainderId).classList.add('invalid');
            } else {
                document.getElementById(remainderId).classList.remove('invalid');
            }
        }

        const calculateExtern = () => calculateRemainder('total_kkt_extern', 'workshop_balance_extern', 'remainder_extern');
        const calculateResident = () => calculateRemainder('total_kkt_resident', 'workshop_balance_resident', 'remainder_resident');

        document.getElementById('total_kkt_extern').addEventListener('input', calculateExtern);
        document.getElementById('workshop_balance_extern').addEventListener('input', calculateExtern);
        document.getElementById('total_kkt_resident').addEventListener('input', calculateResident);
        document.getElementById('workshop_balance_resident').addEventListener('input', calculateResident);

        calculateExtern();
        calculateResident();
    </script>
@endpush