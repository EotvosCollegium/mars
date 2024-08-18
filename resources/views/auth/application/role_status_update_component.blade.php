<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;">
    <div wire:poll.1000ms>
        @if($application->admitted)
                <div class="switch">
                    <label>
                        Bejáró
                        <input type="checkbox" wire:click="switchResidentRole()" @checked($application->admitted_for_resident_status) />
                        <span class="lever"></span>
                        Bentlakó
                    </label>
                </div>
        @endif
        @if($this->updated)
            <label> Státusz frissítve! </label>
        @endif
    </div>
</div>

