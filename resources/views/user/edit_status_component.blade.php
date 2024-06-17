<tr>
    <td>
        <b>{{$semester->tag}} @if($semester == \App\Models\Semester::current()) (jelenlegi) @endif</b>
    </td>
    @can('updateStatus', $user)
    <td>
        <input wire:model.live="comment" wire:change.debounce.150ms="save" placeholder="Megjegyzés" />
    </td>
    @endcan
    <td>
        <span class="new badge {{ \App\Models\SemesterStatus::color($status) }} right" data-badge-caption="">
            <b> @if($status) 
                @lang("user." . $status)
                @else
                @lang("user.no_status")
                @endif
                @if($comment) 
                ({{$comment}}) 
                @endif
            </b>
        </span>
    </td>
    @can('updateStatus', $user)
    <td>  
    <button
            class="red tooltipped waves-effect btn-floating right"
            style="margin-right: 5px; margin-left: 5px;"
            wire:click="removeStatus"
            data-position="bottom"
            data-tooltip="Státusz törlése">
                <i class="material-icons">close</i>
        </button>
        <button
            class="gray tooltipped waves-effect btn-floating right"
            style="margin-right: 5px; margin-left: 5px;"
            wire:click="setStatus('PASSIVE')"
            data-position="bottom"
            data-tooltip="{{__('user.PASSIVE')}}">
                <i class="material-icons">person_off</i>
        </button>
        <button
            class="green tooltipped waves-effect btn-floating right"
            style="margin-right: 5px; margin-left: 5px;"
            wire:click="setStatus('ACTIVE')"
            data-position="bottom"
            data-tooltip="{{__('user.ACTIVE')}}">
                <i class="material-icons">person</i>
        </button>
       
    </td>
    @endcan

</tr>
