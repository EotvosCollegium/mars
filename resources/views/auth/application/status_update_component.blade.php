<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;">
    <label>
        <input type="checkbox"
               wire:click="callIn('{{$workshop->workshop_id}}')" @checked($workshop->called_in) @disabled($workshop->admitted) />
        <span>Behívva</span>
    </label>
    <label>
        <input type="checkbox"
               wire:click="admit('{{$workshop->workshop_id}}')" @checked($workshop->admitted) />
        <span>Felvéve</span>
    </label>
    <div wire:poll.1000ms>
        @if($this->updated)
            <label> Státusz frissítve! </label>
        @endif
    </div>
</div>

