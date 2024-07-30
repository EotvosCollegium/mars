<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('internet.report_fault')</span>
        <blockquote>
            @lang('internet.report_fault_desc')
        </blockquote>
        @if(user()->room && user()->room()->first()?->routers()?->first()?->isDown())
            <blockquote class="error">
                @lang('internet.report_fault_router_unavailable', ['room' => user()->room])
            </blockquote>
        @endif
        <form action="{{ route('internet.report_fault') }}" method="post">
            <div class="form-row align-items-center">
                @csrf
                <div class="row">
                    <x-input.text id="report" required text="internet.report"/>
                    <x-input.text id="error_message" text="internet.report_fault_error_message"/>
                    <x-input.text id="when" required text="internet.report_fault_when"/>
                    <x-input.text id="tries" text="internet.report_fault_tries"/>
                    <x-input.text id="user_os" required text="internet.report_fault_os"
                                  placeholder="pl. Windows 11 / Android 12 / MacOS / Ubuntu"/>
                    <x-input.text id="room" text="general.room" :value="user()->room"/>
                    <x-input.text l=6 id="availability" text="internet.report_fault_availability"/>
                    <x-input.checkbox l=6 id="can_enter" checked text="internet.report_fault_can_enter_room"/>
                    <x-input.button class="right" text="general.send"/>
                </div>
            </div>
        </form>
    </div>
</div>
