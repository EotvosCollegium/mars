<ul id="{{ 'ranking-' . $question->formKey() }}" class="collection container2">
    @foreach($question->options()->get() as $option)
    <li class="collection-item" data-id="{{ $option->id }}">{{ $option->title }}</li>
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
        const input = document.querySelector("input[name='{{ $name ?? $question->formKey() }}']");

        const sortable = new Sortable(ranking, {
            delay: 100,
            delayOnTouchOnly: true,
            store: {
                get: (sortable) => input.value != "" ? JSON.parse(input.value).map(String) : null,
                set: (sortable) => input.value = JSON.stringify(sortable.toArray().map(Number)),
            }
        });
    });
</script>
@endpush
