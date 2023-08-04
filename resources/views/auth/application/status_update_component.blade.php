<div class="right">
    @foreach (\App\Models\ApplicationForm::STATUSES as $st)
    @if($st != \App\Models\ApplicationForm::STATUS_IN_PROGRESS || user()->isAdmin())

    <p style="margin:5px">
        <label>
            <input type="radio" wire:click="set('{{$st}}')" name="{{$this->application->id}}_status" @checked($this->application->status == $st) />
            <span>@include('auth.application.status', ['status' => $st])</span>
        </label>
    </p>
    @endif

    @endforeach
    @if (session()->has('message'))
        <script>
            M.toast({html: "{{ session('message') }}"});
        </script>
    @endif
</div>
