<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Mail\InternetFault;
use App\Mail\MacNeedsApproval;
use App\Models\InternetAccess;
use App\Models\MacAddress;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use App\Models\WifiConnection;
use App\Utils\TabulatorPaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InternetController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:possess,App\Models\InternetAccess');
    }

    public function index()
    {
        $internetAccess = user()->internetAccess;

        return view('network.internet.app', ['internet_access' => $internetAccess]);
    }

    public function admin()
    {
        $this->authorize('handleAny', InternetAccess::class);

        $activationDate = self::getInternetDeadline();
        $users = User::withRole(Role::INTERNET_USER)->with('internetAccess.wifiConnections')->get();

        return view('network.manage.app', ['activation_date' => $activationDate, 'users' => $users]);
    }

    public function getUsersMacAddresses(Request $request)
    {
        $paginator = TabulatorPaginator::from(user()->macAddresses())
            ->sortable(['mac_address', 'comment', 'state'])->paginate();

        $paginator->getCollection()->transform($this->translateStates());

        return $paginator;
    }

    public function getUsersMacAddressesAdmin(Request $request)
    {
        $this->authorize('viewAny', MacAddress::class);

        $paginator = TabulatorPaginator::from(MacAddress::join('users as user', 'user.id', '=', 'user_id')
            ->join('internet_accesses as internet', 'internet.user_id', '=', 'user.id')
            ->select('mac_addresses.*', 'internet.wifi_username')->with('user'))
            ->sortable(['mac_address', 'comment', 'state', 'user.name', 'created_at', 'wifi_username'])
            ->filterable(['mac_address', 'comment', 'user.name', 'state', 'created_at', 'wifi_username'])
            ->paginate();

        $paginator->getCollection()->transform($this->translateStates());

        return $paginator;
    }

    public function getInternetAccessesAdmin()
    {
        $this->authorize('viewAny', InternetAccess::class);

        $paginator = TabulatorPaginator::from(InternetAccess::join('users as user', 'user.id', '=', 'user_id')->select('internet_accesses.*')->with('user'))
            ->sortable(['auto_approved_mac_slots', 'has_internet_until', 'user.name'])
            ->filterable(['auto_approved_mac_slots', 'has_internet_until', 'user.name'])
            ->paginate();

        return $paginator;
    }

    public function deleteMacAddress(Request $request, $id)
    {
        $macAddress = MacAddress::findOrFail($id);

        $this->authorize('delete', $macAddress);

        $macAddress->delete();
    }

    public function resetWifiPassword(Request $request)
    {
        user()->internetAccess->resetPassword();

        return redirect()->back();
    }

    public function editMacAddress(Request $request, $id)
    {
        $macAddress = MacAddress::findOrFail($id);

        $this->authorize('update', $macAddress);

        if ($request->has('state')) {
            $macAddress->state = strtoupper($request->input('state'));
        }

        $macAddress->save();

        $macAddress = $macAddress->refresh(); // auto approve maybe modified this

        return $this->translateStates()($macAddress);
    }

    public function editInternetAccess(Request $request, $id)
    {
        $internetAccess = InternetAccess::findOrFail($id);

        $this->authorize('update', $internetAccess);

        if ($request->has('has_internet_until')) {
            $internetAccess->has_internet_until = $request->input('has_internet_until');
        }

        if ($request->has('auto_approved_mac_slots')) {
            $internetAccess->auto_approved_mac_slots = max(0, $request->input('auto_approved_mac_slots'));
        }

        $internetAccess->save();

        return InternetAccess::join('users as user', 'user.id', '=', 'user_id')
            ->select('internet_accesses.*')->with('user')
            ->where('user_id', '=', $internetAccess->user_id)->first();
    }

    public static function extendUsersInternetAccess(User $user)
    {
        $internetAccess = $user->internetAccess;

        if ($internetAccess != null) {
            $internetAccess->update(['has_internet_until' => self::getInternetDeadline()]);

            return $internetAccess->has_internet_until;
        } else {
            return null;
        }
    }

    public function addMacAddress(Request $request)
    {
        $this->authorize('create', MacAddress::class);

        $validator = Validator::make($request->all(), [
            'comment' => 'required|max:255',
            'user_id' => 'nullable|exists:users,id',
            'mac_address' => ['required', 'regex:/((([a-fA-F0-9]{2}[-:]){5}([a-fA-F0-9]{2}))|(([a-fA-F0-9]{2}:){5}([a-fA-F0-9]{2})))/i'],
        ]);
        $validator->validate();

        if (user()->can('accept', MacAddress::class) && $request->has('user_id')) {
            $target_id = $request->input('user_id');
            $state = MacAddress::APPROVED;
        } else {
            $target_id = user()->id;
            $state = MacAddress::REQUESTED;

            foreach (User::admins() as $admin) {
                Mail::to($admin)->send(new MacNeedsApproval($admin->name, user()->name));
            }
        }

        MacAddress::create([
            'user_id' => $target_id,
            'state' => $state,
            'comment' => $request->input('comment'),
            'mac_address' => $request->input('mac_address'), //TODO use mutator
        ]);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    public function getWifiConnectionsAdmin(Request $request)
    {
        $this->authorize('viewAny', WifiConnection::class);

        $paginator = TabulatorPaginator::from(
            WifiConnection::query()
                ->groupBy(['wifi_username', 'mac_address', 'ip', 'lease_start', 'lease_end', 'note'])
                ->select(['wifi_username', 'mac_address', 'ip', 'lease_start', 'lease_end', 'note', DB::raw('COUNT(*) as radius_connections')])
            )->sortable(['wifi_username', 'mac_address', 'ip', 'lease_start'])
            ->filterable(['wifi_username', 'mac_address', 'ip', 'lease_start'])
            ->paginate();

        return $paginator;
    }

    public function translateStates(): \Closure
    {
        return function ($data) {
            $data->state = strtoupper($data->state);
            $data->_state = $data->state;
            switch ($data->state) {
                case MacAddress::APPROVED:
                    $data->state = __('internet.approved');
                    break;
                case MacAddress::REJECTED:
                    $data->state = __('internet.rejected');
                    break;
                case MacAddress::REQUESTED:
                    $data->state = __('internet.requested');
                    break;
            }

            return $data;
        };
    }

    /**
     * Sends an email to all admins with the report of a fault.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reportFault(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report' => 'required|string',
            'user_os' => 'required|string',
        ]);
        $validator->validate();

        foreach (User::withRole(Role::SYS_ADMIN)->get() as $admin) {
            Mail::to($admin)->queue(new InternetFault($admin->name, user()->name, $request->report, $request->user_os));
        }
        return redirect()->back()->with('message', __('mail.email_sent'));
    }

    private static function getInternetDeadline()
    {
        return Semester::next()->getStartDate()->addMonth();
    }
}
