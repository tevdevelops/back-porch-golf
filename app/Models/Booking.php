<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * @property string $id
 * @property int $bay_id
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property int $players
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property BookingStatus $status
 * @property CarbonImmutable|null $hold_expires_at
 * @property int $subtotal_cents
 * @property int $tax_cents
 * @property int $total_cents
 * @property string|null $stripe_checkout_session_id
 * @property string|null $stripe_payment_intent_id
 * @property string|null $door_code
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $refunded_at
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'hold_expires_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'refunded_at' => 'immutable_datetime',
            'status' => BookingStatus::class,
            'players' => 'integer',
            'subtotal_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<Bay, $this> */
    public function bay(): BelongsTo
    {
        return $this->belongsTo(Bay::class);
    }

    /**
     * Bookings that keep a slot taken: Confirmed ones, and Held ones whose
     * Hold has not run out.
     *
     * @param  Builder<Booking>  $query
     */
    public function scopeBlocking(Builder $query, ?CarbonImmutable $now = null): void
    {
        $now ??= now();

        $query->where(function (Builder $q) use ($now) {
            $q->where('status', BookingStatus::Confirmed)
                ->orWhere(fn (Builder $held) => $held
                    ->where('status', BookingStatus::Held)
                    ->where('hold_expires_at', '>', $now));
        });
    }

    /**
     * Bookings whose time overlaps [start, end).
     *
     * @param  Builder<Booking>  $query
     */
    public function scopeOverlapping(Builder $query, CarbonImmutable $start, CarbonImmutable $end): void
    {
        $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function minutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }

    public function accessStartsAt(): CarbonImmutable
    {
        return $this->starts_at->subMinutes(config('studio.door_code_lead_minutes'));
    }

    public function canBeCancelled(?CarbonImmutable $now = null): bool
    {
        $now ??= now();

        return $this->status === BookingStatus::Confirmed
            && $now->lte($this->starts_at->subHours(config('studio.cancel_cutoff_hours')));
    }

    /** Signed link to the customer's booking page (no accounts in v1). */
    public function manageUrl(): string
    {
        return URL::signedRoute('bookings.show', $this);
    }

    public function localStart(): CarbonImmutable
    {
        return $this->starts_at->setTimezone(config('studio.timezone'));
    }

    public function localEnd(): CarbonImmutable
    {
        return $this->ends_at->setTimezone(config('studio.timezone'));
    }
}
