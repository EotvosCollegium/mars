<?php

namespace App\Http\Controllers\Dormitory\Reservations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Enums\ReservableItemType;
use App\Models\Reservations\ReservableItem;
use App\Models\Role;
use App\Models\User;
use App\Mail\Reservations\ReservationAffected;
use App\Mail\Reservations\ReportReservableItemFault;

class ReservableItemController extends \App\Http\Controllers\Controller
{
    /**
     * Lists all reservable items
     * of the requested type
     * (or both if there is none given).
     */
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'type' => [
                'required',
                Rule::enum(ReservableItemType::class)
            ]
        ]);

        $stringType = $validatedData['type'];
        $this->authorize('viewType', [
            ReservableItem::class,
            ReservableItemType::from($stringType)
        ]);

        $items = ReservableItem::where('type', $stringType)->get();
        return view('dormitory.reservations.items.index', [
            'items' => $items,
            'type' => $stringType
        ]);
    }

    /**
     * Shows the details of a given item,
     * along with the corresponding timetable.
     */
    public function show(ReservableItem $item)
    {
        $this->authorize('view', $item);

        return view('dormitory.reservations.items.show', [
            'item' => $item,
        ]);
    }

    /**
     * Shows the timetable for a given item
     * in an easily printable format.
     */
    public function showPrintVersion(ReservableItem $item)
    {
        $this->authorize('view', $item);

        return view('dormitory.reservations.items.show_print_version', [
            'item' => $item,
        ]);
    }

    /**
     * Returns the item creation page.
     */
    public function create()
    {
        $this->authorize('administer', ReservableItem::class);

        abort(501, 'create not implemented yet');
    }

    /**
     * Stores a new item.
     */
    public function store(Request $request)
    {
        $this->authorize('administer', ReservableItem::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => Rule::in([ReservableItemType::WASHING_MACHINE->value, ReservableItemType::ROOM->value]),
            'out_of_order' => 'nullable|boolean',
        ]);

        $validatedData = $validator->validate();

        $newItem = ReservableItem::create($validatedData);

        return redirect()->route('reservations.items.show', $newItem);
    }

    /**
     * Deletes an item.
     */
    public function delete(ReservableItem $item)
    {
        $this->authorize('administer', ReservableItem::class);

        $item->delete();
        return redirect(route('reservations.items.index'));
    }

    /**
     * Sends admins and staff an email notification
     * about an allegedly faulty item,
     * with a message provided in a modal dialog.
     */
    public function reportFault(ReservableItem $item, Request $request)
    {
        $this->authorize('view', $item);

        $validatedData = $request->validate([
            'message' => 'nullable|max:2047'
        ]);

        $thoseToNotify = User::whereHas('roles', function ($query) {
            $query->whereIn('name', [Role::SYS_ADMIN, Role::STAFF]);
        })->get();
        foreach ($thoseToNotify as $toNotify) {
            Mail::to($toNotify)->queue(
                new ReportReservableItemFault(
                    $item,
                    $toNotify->name,
                    user()->name,
                    $validatedData['message']
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

        foreach ($item->usersWithActiveReservation()->get() as $toNotify) {
            Mail::to($toNotify)->queue(new ReservationAffected(
                $item,
                $toNotify->name
            ));
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
