<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Enums\ReservableItemType;
use App\Models\ReservableItem;
use App\Models\Role;
use App\Models\User;
use App\Mail\AffectedReservation;
use App\Mail\ReportReservableItemFault;

class ReservableItemController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ReservableItem::class);

        $validatedData = $request->validate([
            'type' => [
                'nullable',
                Rule::enum(ReservableItemType::class)
            ]
        ]);

        if (!array_key_exists('type', $validatedData)) {
            $items = ReservableItem::all();
        } else {
            $items = ReservableItem::where('type', $validatedData['type'])->get();
        }
        return view('reservations.items.index', [
            'items' => $items
        ]);
    }

    /**
     * Lists reservations for all washing machines.
     */
    public function indexForWashingMachines()
    {
        $this->authorize('viewAny', ReservableItem::class);

        $items = ReservableItem::where('type', ReservableItemType::WASHING_MACHINE)->get();
        return view('reservations.items.index_for_washing_machines', [
            'items' => $items->all()
        ]);
    }

    public function show(ReservableItem $item)
    {
        $this->authorize('viewAny', ReservableItem::class);

        return view('reservations.items.show', [
            'item' => $item,
        ]);
    }

    public function create()
    {
        $this->authorize('administer', ReservableItem::class);

        abort(500, 'create not implemented yet');
    }

    public function store(Request $request)
    {
        $this->authorize('administer', ReservableItem::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => Rule::in(["washing_machine", "room"]),
            'out_of_order' => 'nullable|boolean',
        ]);

        $validatedData = $validator->validate();

        $newItem = ReservableItem::create($validatedData);

        return redirect()->route('reservations.items.show', $newItem);
    }

    public function delete(ReservableItem $item)
    {
        $this->authorize('administer', ReservableItem::class);

        $item->delete();
        return redirect(route('reservations.items.index'));
    }

    /**
     * Sends admins and staff an email notification
     * about an allegedly faulty item.
     */
    public function reportFault(ReservableItem $item)
    {
        $this->authorize('requestReservation', $item);

        $thoseToNotify = User::withRole(Role::SYS_ADMIN)->get()
            ->concat(User::withRole(Role::STAFF)->get());
        foreach ($thoseToNotify as $toNotify) {
            Mail::to($toNotify)->send(
                new ReportReservableItemFault(
                    $item,
                    $toNotify->name,
                    user()->name
                )
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

        $item->update(['out_of_order' => !$item->out_of_order]);

        foreach ($item->usersWithActiveReservation as $toNotify) {
            Mail::to($toNotify)->send(new AffectedReservation(
                $item,
                $toNotify->name
            ));
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
