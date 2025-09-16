<div wire:poll>
    @can('administer', \App\Models\GeneralAssemblies\GeneralAssembly::class)
    <div
        @if($isFullscreen)class="center" style="font-size: 15em;"@endif
    >
    @if(!$isFullscreen)@lang('voting.passcode'): @endif<span style="font-family: sans-serif;">{{$this->passcode}}</span>
    </div>
    @endcan
</div>
