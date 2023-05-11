@foreach ($categories as $category)
    <div class="row scale-transition" style="margin:0">
        <div class="col s5" style="padding: 0.8rem;">
            {{ $category->title }}
        </div>
        <div class="col s6">
            <div
            id="select-ui-{{ $category->id }}"
            @if ($category->custom_name !== null)
                hidden
            @endif
            >
            <x-input.select only-input :id="'select-' . $category->id" :elements="$users" without-placeholder allow-empty without-label
                :default="$category->votee_id"
                />
            </div>
            <textarea id="raw-{{ $category->id }}" name="raw-{{ $category->id }}" class="materialize-textarea mr-textarea"
            @if ($category->custom_name === null)
                hidden
            @endif
            >{{ $category->custom_name }}</textarea>
        </div>
        <div class="col s1">
            <button class="btn-floating waves-effect waves-light right input-changer" id="button-{{ $category->id }}">
                <i class="material-icons" data-number="{{ $category->id }}">border_color</i>
            </button>
        </div>
    </div>
@endforeach

<button class="btn waves-effect waves-light" type="submit" id="mr-submit" name="action">Mentés
    <i class="material-icons right">save</i>
</button>
