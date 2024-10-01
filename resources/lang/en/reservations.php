<?php

return [
    'all_affected' => 'This affects the entire recurring reservation.', // in mailable ReservationDeleted
    'already_exists' => 'Reservation already exists in the given interval:',
    'already_verified' => 'This reservation has already been verified.',
    'bad_for_non-recurring' => 'Bad request for non-recurring reservation.',
    'check_reservations' => 'Please organize your reservations accordingly.',
    'create' => 'New reservation', // page title in breadcrumb
    'describe_what_happened' => 'Describe what happened in a few sentences.',   // when reporting a fault
    'edit' => 'Edit reservation', // a page title
    'edit_all' => 'For all reservations',
    'edit_all_after' => 'For this reservation and all after this one',
    'edit_this_only' => 'For this reservation only',
    'editing_past_reservations' => 'You cannot edit reservations that are already in the past.',
    'faulty_room' => 'Unusable room',
    'faulty_washing_machine' => 'Faulty washing machine',
    'frequency' => 'Frequency (in days)',
    'frequency_comment' => '-day cycle',
    'from' => 'From',
    'got_repaired' =>
        'The following reservable item (for which you have a reservation) has become usable again',
    'has_approved_your_reservation' => 'has approved your reservation.',
    'has_become_faulty' =>
        'The following reservable item (for which you have a reservation) has become faulty or unusable:',
    'has_deleted_your_reservation' => 'has <strong>deleted</strong> your reservation.',
    'has_rejected_your_reservation' =>
        'has <strong>rejected</strong> your reservation.',
    'is_free' => 'Free',
    'is_occupied' => 'Occupied',
    'is_recurring' => 'Is recurring?',
    'is_verified' => 'Is verified?',
    'item_name' => 'Name',
    'item_status' => 'Status',
    'last_day' => 'Last day',
    'max_washing_reservations_reached' =>
        'You have reached the maximum amount of active reservations for washing machines.',
    'no_recurring_for_washing_machines' => 'Recurring reservations are not allowed for washing machines.',
    'not_a_recurring_reservation' => 'Not a recurring reservation.',
    'note' => 'Note',
    'note_disclaimer' => 'Notes can be seen by anyone who can view the timetable.',
    'one_hour_slot_only' => 'For washing machines, only one-hour slots can be reserved.',
    'only_this_affected' => 'This only affects this specific session.', // in mailable ReservationDeleted
    'out_of_order' => 'Out of order',
    'recurring' => 'Recurring reservation',
    'recurring_conflict' => 'There is a conflict for one of the recurring events:',
    'repaired_room' => 'Room usable again',
    'repaired_washing_machine' => 'Washing machine repaired',
    'report_fault' => 'Report fault',
    'report_fix' => 'Report fix',
    'reservation_deleted' => 'Reservation deleted', // an e-mail subject
    'reservation_rejected' => 'Reservation rejected', // an e-mail subject too
    'reservation_verified' => 'Reservation verified', // an e-mail subject too
    'reservations' => 'Reservations',
    'room' => 'Room',
    'rooms' => 'Rooms',
    'room_index_instructions' => 'If a room seems to be unusable (e.g. because of renovation),
        please notify the admins with the button next to its name.
        If it is usable again, click the same button.',
    'room_reservation_instructions' =>
        'Only workshop administrators, workshop leaders and Students\' Council members can request reservations.
         To make a reservation, click a green rectangle approximately corresponding to the wished time window.
         You can then set the exact starting and ending times.<br />
         For a detailed timetable of a specific room, click the name of that room.
         You can choose a specific day by clicking the date above the table.',
    'room_reservation_not_open' =>
        'Currently, only the secretariat and some other authorized people can make reservations.',
    'room_reservations' => 'Room reservations',
    'set_fixed' => 'Set fixed',
    'set_out_of_order' => 'Set out of order',
    'title' => 'Title',
    'until' => 'Until',
    'unverified' => 'not yet verified', // as a status
    'verifiers_notified' =>
        'The secretariat and staff have been notified. Please wait until they approve your reservation.',
    'verify' => 'Verify',
    'verify_all' => 'Verify all',
    'washing_machine' => 'Washing machine',
    'washing_machines' => 'Washing machines',
    'washing_machine_index_instructions' => 'If a machine has become faulty,
        please notify the admins with the button next to its name.
        If it is usable again, click the same button.',
    'washing_machine_reservation_instructions' =>
        'To reserve a slot, click the green rectangle representing it.<br />
         You can have at most 6 active reservations. To report a fault, scroll down to the bottom of the page.<br />
         You can choose a specific day by clicking the date above the table.',
    'washing_machine_reservations' => 'Washing reservations'
];
