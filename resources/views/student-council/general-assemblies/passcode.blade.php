<div wire:poll>
    @can('administer', \App\Models\GeneralAssemblies\GeneralAssembly::class)
    <div
        @if($isFullscreen)class="center" style="font-size: 15em;"@endif
    >
    @lang('voting.passcode'): <span style="font-family: Monospace;">{{$this->passcode}}</span>
    </div>
    @endcan
</div>
