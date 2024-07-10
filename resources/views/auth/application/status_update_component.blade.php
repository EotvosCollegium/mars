<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;">
    <label>
        <input type="checkbox"
               wire:click="callIn('{{$workshop->workshop_id}}')" @checked($workshop->called_in) />
        <span>Behívva</span>
    </label>
    <label>
        <input type="checkbox"
               wire:click="admit('{{$workshop->workshop_id}}')" @checked($workshop->admitted) />
        <span>Felvéve</span>
    </label>


    @if (session()->has('message'))
        <script>
            M.toast({html: "{{ session('message') }}"});
        </script>
    @endif
</div>

