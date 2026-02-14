<div class="card">
    <div class="card-content">
        <a href="{{ route('kktnetreg') }}" class="btn waves-effect right">KKT fizetők listája</a>
        <span class="card-title">KKT fizetése</span>
        <div class="row">
            <div class="col s12">
                <blockquote>
                    <p>Ha valaki fizetni szeretne neked, azt írd fel itt. Csak aktív státuszú collegisták választhatóak ki, akik még nem fizettek KKT-t.</p>
                    <p>
                        A fizetendő összeg
                        a <strong>bentlakók</strong> (és BB-sek, collegista vendégek) esetén <strong>{{ $total_kkt_resident }}&nbsp;Ft</strong>,
                        a <strong>bejárók</strong> esetén <strong>{{ $total_kkt_extern }}&nbsp;Ft</strong>.
                    </p>
                    <p>A tranzakcióról emailben értesítést kapnak, és az internet-elérésük automatikusan meghosszabbításra kerül.</p>
                </blockquote>
            </div>
        </div>
        <form method="POST" action="{{ route('kktnetreg.pay') }}">
            @csrf
            <div class="row valign-wrapper" style="flex-wrap: wrap">
                <x-input.select m=5 :elements="$users_not_paid" id="user_id" text="Fizető" :formatter="function ($user) { return $user->uniqueName; }" />
                <div class="col s12 m7" style="font-size: 1.15rem; font-weight: 400">
                    Fizetendő: <span class="coli-text text-orange"><span id="kkt-to-pay">-</span> Ft</span>
                </div>
                <input type="hidden" name="calculated_amount" id="calculated_amount" value="">
            </div>
            <x-input.button floating class="btn-large right" icon="send" />
        </form>
    </div>
</div>

@push('scripts')
    <script>
        const resident_ids = @json($kkt_non_payers_with_rooms);

        function setPayableAmount(user_id) {
            const displayElement = document.getElementById('kkt-to-pay');
            const checkElement = document.getElementById('calculated_amount');
            const amount = resident_ids.includes(user_id) ? {{ $total_kkt_resident }} : {{ $total_kkt_extern }};
            displayElement.textContent = amount;
            checkElement.value = amount;
        }

        document.getElementById('user_id').addEventListener('change', (event) =>
            setPayableAmount(event.target.value)
        );

        // Set initial amount if there's a preselected user (e.g. after validation error)
        const userSelect = document.getElementById('user_id');
        if (userSelect.value) {
            setPayableAmount(userSelect.value);
        }
    </script>
@endpush
