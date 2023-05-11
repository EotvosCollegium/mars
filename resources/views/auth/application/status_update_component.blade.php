<div class="right">
    @foreach (\App\Models\ApplicationForm::STATUSES as $st)
    <p style="margin:5px">
        <label>
            <input type="radio" wire:click="set('{{$st}}')" name="{{$this->application->id}}_status" @checked($this->application->status == $st) />
            <span>@include('auth.application.status', ['status' => $st])</span>
        </label>
    </p>
    @endforeach
    @if (session()->has('message'))
        <script>
            M.toast({html: "{{ session('message') }}"});
        </script>
    @endif
</div>