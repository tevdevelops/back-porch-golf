<x-mail::message>
# You're booked, {{ $booking->customer_name }}

**{{ $bayName }}** · {{ $when }}<br>
{{ $booking->players }} {{ Str::plural('player', $booking->players) }} · ${{ $total }} paid

<x-mail::panel>
Your door code: **{{ $booking->door_code }}**

It opens the studio door from {{ $doorOpens }} until your booking ends.
</x-mail::panel>

Need to cancel? You can cancel for a full refund up to {{ config('studio.cancel_cutoff_hours') }} hours before you start.

<x-mail::button :url="$manageUrl">
View or cancel your booking
</x-mail::button>

See you on the porch,<br>
{{ config('studio.name') }}
</x-mail::message>
