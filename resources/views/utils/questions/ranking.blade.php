<ul id="{{ 'ranking-' . $question->formKey() }}" class="collection rcv-list rcv-ranking"></ul>

<hr style="border-top: 1.5px solid #666;">

<ul id="{{ 'abstention-' . $question->formKey() }}" class="collection rcv-list rcv-abstention">
    @foreach($question->options()->get() as $option)
    <li class="collection-item row" data-id="{{ $option->id }}">
        <div class="col s8 m10" style="padding: 10px 0">
            <span style="display: inline-block; width: 1.5em" class="rcv-idx"></span>
            {{ $option->title }}
        </div>
        <div class="col s2 m1">
            <button type="button" class="rcv-list-up btn-floating waves-effect waves-light right"><i class="material-icons">keyboard_arrow_up</i></button>
        </div>
        <div class="col s2 m1">
            <button type="button" class="rcv-list-down btn-floating waves-effect waves-light right"><i class="material-icons">keyboard_arrow_down</i></button>
        </div>
    </li>
    @endforeach
</ul>

<input type="hidden" name="{{ $name ?? $question->formKey() }}" value="{{ old($question->formKey()) }}">


@push('scripts')
@once
<script type="text/javascript" src="{{ mix('js/Sortable.min.js') }}"></script>
@endonce

<script>
    addEventListener("DOMContentLoaded", (event) => {
        const ranking = document.getElementById("{{ 'ranking-' . $question->formKey() }}");
        const abstention = document.getElementById("{{ 'abstention-' . $question->formKey() }}");
        const input = document.querySelector("input[name='{{ $name ?? $question->formKey() }}']");

        const positionChanged = (evt) => {
            const items = ranking.querySelectorAll(".collection-item");
            items.forEach((item, idx) => item.querySelector(".rcv-idx").textContent = `${idx + 1}.`);

            const abstentions = abstention.querySelectorAll(".collection-item");
            abstentions.forEach((item, idx) => item.querySelector(".rcv-idx").textContent = "");
        };

        const sortable = new Sortable(ranking, {
            group: "{{ $question->formKey() }}",
            delay: 100,
            delayOnTouchOnly: true,
            store: {
                get: (sortable) => input.value != "" ? JSON.parse(input.value).map(String) : null,
                set: (sortable) => input.value = JSON.stringify(sortable.toArray().map(Number)),
            },
            onChange: positionChanged,
            onEnd: positionChanged,
        });
        input.value = JSON.stringify(sortable.toArray().map(Number));

        new Sortable(abstention, {
            group: "{{ $question->formKey() }}",
            delay: 100,
            delayOnTouchOnly: true,
            sort: false,
            onChange: positionChanged,
            onEnd: positionChanged,
        });

        document.querySelectorAll(".rcv-list-up").forEach((button) => {
            button.addEventListener("click", (evt) => {
                const item = evt.target.closest(".collection-item");
                if (evt.target.closest(".rcv-abstention")) {
                    ranking.appendChild(item);
                } else {
                    const prev = item.previousElementSibling;
                    if (prev) {
                        item.parentNode.insertBefore(item, prev);
                    }
                }

                positionChanged();
                sortable.save();
            });
        });

        document.querySelectorAll(".rcv-list-down").forEach((button) => {
            button.addEventListener("click", (evt) => {
                const item = evt.target.closest(".collection-item");
                if (evt.target.closest(".rcv-abstention")) return;

                const next = item.nextElementSibling;
                if (!next) {
                    abstention.appendChild(item);
                } else {
                    item.parentNode.insertBefore(next, item);
                }

                positionChanged();
                sortable.save();
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
.rcv-list:not(:has(*)) {
    display: block;
    padding: 1em;
    text-align: center;
    color: #999;
    border: 1px dashed #999;
    border-radius: 4px;
    margin: 0.5em 0;
}

.rcv-ranking:not(:has(*))::after {
    content: "{{ __('voting.drag_to_rank') }}";
}

.rcv-abstention:not(:has(*))::after {
    content: "{{ __('voting.drag_to_exclude') }}";
}

.rcv-abstention .collection-item {
    background-color: #f5f5f5;
}
</style>
@endpush
