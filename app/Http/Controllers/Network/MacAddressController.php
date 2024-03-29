<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Mail\MacNeedsApproval;
use App\Mail\MacStatusChanged;
use App\Models\Internet\InternetAccess;
use App\Models\Internet\MacAddress;
use App\Models\User;
use App\Utils\TabulatorPaginator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class MacAddressController extends Controller
{
    /**
     * Get paginated mac addresses data.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $this->authorize('handleAny', InternetAccess::class);

        return TabulatorPaginator::from(
            MacAddress::query()
                ->join('users as user', 'user.id', '=', 'user_id')
                ->select('mac_addresses.*')
                ->with('user')
        )
            ->sortable(['mac_address', 'comment', 'state', 'user.name', 'created_at'])
            ->filterable(['mac_address', 'comment', 'user.name', 'state', 'created_at'])
            ->paginate();
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MacAddress::class);

        $request->validate([
            'comment' => 'required|max:255',
            'mac_address' => ['required', 'regex:/((([a-fA-F0-9]{2}[-:]){5}([a-fA-F0-9]{2}))|(([a-fA-F0-9]{2}:){5}([a-fA-F0-9]{2})))/i'],
        ]);

        MacAddress::create([
            'user_id' => user()->id,
            'comment' => $request->input('comment') ?? '',
            'mac_address' => $request->input('mac_address'),
            'state' => user()->isAdmin() ? MacAddress::APPROVED : MacAddress::REQUESTED
        ]);

        foreach (User::admins() as $admin) {
            Mail::to($admin)->queue(new MacNeedsApproval($admin->name, user()->name));
        }

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Updates the status of a mac address.
     * @throws AuthorizationException
     */
    public function update(Request $request, MacAddress $macAddress): MacAddress
    {
        $this->authorize('update', $macAddress);

        $data = $request->validate([
            'comment' => 'nullable|string|max:255',
            'state' => 'nullable|in:' . implode(',', MacAddress::STATES)
        ]);

        $macAddress->update($data);
        if ($request->has('state')) {
            $user = $macAddress->internetAccess->user;
            Mail::to($user)->queue(new MacStatusChanged($user->name, $macAddress));
        }

        return $macAddress;
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(MacAddress $macAddress): Response
    {
        $this->authorize('delete', $macAddress);

        $macAddress->delete();

        return response("", 204);
    }
}
