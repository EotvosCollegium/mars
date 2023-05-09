<div wire:poll>
    @can('administer', \App\Models\GeneralAssemblies\GeneralAssembly::class)
    @lang('voting.passcode'): <span style="font-family: Monospace;">{{$this->passcode}}</span>
    @endcan
</div>
