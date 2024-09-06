<?php

namespace App\Enums;

/**
 * The possible types of a reservable item
 * (washing machine or room).
 */
enum ReservableItemType: string
{
    case WASHING_MACHINE = 'washing_machine';
    case ROOM = 'room';
}
