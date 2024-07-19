<div class="right">
    <div>
        @foreach (\App\Models\ApplicationForm::STATUSES as $st)
        @if($st != \App\Models\ApplicationForm::STATUS_IN_PROGRESS || user()->isAdmin())
        <div>
            {{-- I simply could not find another way,
                    neither @checked nor native PHP worked --}}
            @if($this->applicationForm->status == $st)
            <x-input.radio
                wire:click="setStatus('{{$st}}')"
                name="status_{{$this->applicationForm->id}}"
                data-position="bottom"
                text="{{__('application.' . $st)}}"
                checked />
            @else
            <x-input.radio
                wire:click="setStatus('{{$st}}')"
                name="status_{{$this->applicationForm->id}}"
                data-position="bottom"
                text="{{__('application.' . $st)}}" />
            @endif
        </div>
        @endif
        @endforeach
    </div>

    @if (session()->has('message'))
        <script>
            M.toast({html: "{{ session('message') }}"});
        </script>
    @endif

</div>
