<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Held = 'held';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
