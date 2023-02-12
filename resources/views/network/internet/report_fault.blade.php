<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('internet.report_fault')</span>
        <blockquote>
            @lang('internet.report_fault_desc')
        </blockquote>
        <form action="{{ route('internet.report_fault') }}" method="post">
            <div class="form-row align-items-center">
                @csrf
                <div class="row">
                    <x-input.text l=7 id="report" placeholder="doesn't connect" required text="internet.report"/>
                    <x-input.text l=3 id="user_os" placeholder="Windows 11" required text="internet.user_os"/>
                    <x-input.button l=2 text="general.send"/>
                </div>
            </div>
        </form>
    </div>
</div>
