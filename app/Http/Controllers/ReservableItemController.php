<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Models\ReservableItem;
use App\Models\Role;
use App\Models\User;
use App\Mail\AffectedReservation;
use App\Mail\ReportReservableItemFault;

class ReservableItemController extends Controller
{
    public function index(Request $request) {
        $this->authorize('viewAny', ReservableItem::class);

        $type = $request->type;
        if (is_null($type)) {
            $items = ReservableItem::all();
        } else if ('washing_machine' == $type) {
            $items = ReservableItem::where('type', 'washing_machine')->get();
        } else if ('room' == $type) {
            $items = ReservableItem::where('type', 'room')->get();
        } else {
            abort(400, "Unknown reservable item type: $type");
        }
        return view('reservations.items.index', [
            'items' => $items
        ]);
    }

    public function show(ReservableItem $item) {
        $this->authorize('viewAny', ReservableItem::class);

        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(6);
        return view('reservations.items.show', [
            'item' => $item,
            'from' => $from,
            'until' => $until
        ]);
    }

    public function create() {
        $this->authorize('administer', ReservableItem::class);

        abort(500, 'create not implemented yet');
    }

    public function store(Request $request) {
        $this->authorize('administer', ReservableItem::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => Rule::in(["washing_machine", "room"]),
            'default_reservation_duration' => 'required|numeric|min:1|max:65535',
            'is_default_compulsory' => 'required|boolean',
            'allowed_starting_minutes' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $minutes = explode(',', $value);
                    foreach ($minutes as $minute) {
                        if (!is_numeric($minute) || intval($minute) < 0 || intval($minute) > 59) {
                            $fail("Invalid \"allowed starting minutes\" list (there is a value that is either not numeric or is not between 0 and 59).");
                        }
                    }
                },
            ],
            'out_of_order' => 'nullable|boolean',
        ]);

        $validatedData = $validator->validate();

        $newItem = ReservableItem::create($validatedData);

        return redirect()->route('reservations.items.show', $newItem);
    }

    public function delete(ReservableItem $item) {
        $this->authorize('administer', ReservableItem::class);

        $item->delete();
        return redirect(route('reservations.items.index'));
    }

    /**
     * Sends admins and staff an email notification
     * about an allegedly faulty item.
     */
    public function reportFault(ReservableItem $item) {
        $this->authorize('requestReservation', $item);

        $thoseToNotify = User::withRole(Role::SYS_ADMIN)->get()
            ->concat(User::withRole(Role::STAFF)->get());
        foreach ($thoseToNotify as $toNotify) {
            Mail::to($toNotify)->send(new ReportReservableItemFault(
                $item,
                $toNotify->name,
                user()->name)
            );
        }

        return redirect()->back()->with('message', __('mail.email_sent'));
    }

    /**
     * Allows an administrator or staff member to set an item to be out of order
     * (potentially after verifying a fault notice),
     * or to set it back.
     */
    public function toggleOutOfOrder(ReservableItem $item)
    {
        $this->authorize('administer', ReservableItem::class);

        $outOfOrder = !($item->out_of_order);
        $item->out_of_order = $outOfOrder;
        $item->save();

        foreach ($item->usersWithActiveReservation as $toNotify) {
            Mail::to($toNotify)->send(new AffectedReservation(
                $item,
                $toNotify->name
            ));
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
