<?php

namespace App\Models;

use Database\Factories\BayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bay extends Model
{
    /** @use HasFactory<BayFactory> */
    use HasFactory;

    protected $fillable = ['name', 'position'];

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
