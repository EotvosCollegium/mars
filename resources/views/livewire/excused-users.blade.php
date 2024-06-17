<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.excused_users')</span>
                <form wire:submit="addUser">
                    <div class="row">
                        <div class="col s12 m6">
                            <div wire:ignore>
                                <x-input.select
                                    id="user"
                                    text="general.members"
                                    wire:model.live="user"
                                    :elements="\App\Models\User::collegists()"
                                    :formatter="fn($user) => $user->uniqueName"
                                    allowEmpty
                                />
                            </div>
                        </div>
                        <div class="col s12 m3">
                            <x-input.text
                                id="comment"
                                text="general.comment"
                                wire:model.live="comment"
                                :helper="__('general.optional_field')"
                            />
                        </div>
                        <div class="col s12 m3">
                            <x-input.button text="general.add" />
                        </div>
                    </div>
                </form>
                <table>
                    <thead>
                    <tr>
                        <th>@lang('voting.name')</th>
                        <th>@lang('general.comment')</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($general_assembly->excusedUsers()->get() as $excusedUser)
                    <tr>
                        <td>{{$excusedUser->uniqueName}}</td>
                        <td>{{$excusedUser->pivot->comment}}</td>
                        <td>
                            <x-input.button
                                type="button"
                                wire:click.prevent="removeUser({{$excusedUser->id}})"
                                floating
                                class="right red"
                                icon="remove"
                            />
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
