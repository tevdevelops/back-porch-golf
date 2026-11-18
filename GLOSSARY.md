# Glossary

The language of Back Porch Golf Co., a made-up two-bay golf simulator studio that is open 24/7 with no staff.

## Bay

One simulator bay. The studio has two. A Bay is what a customer reserves, never a single seat or a start time.

## Booking

One customer's reservation of one Bay for a continuous block of time, paid in full up front. Customers see it as "booking a bay"; avoid "tee time", which on a real course means a start time rather than a block of hours.

A Booking is always in exactly one of these states:

- **Held**: created, awaiting payment. Blocks its slot until its Hold runs out.
- **Confirmed**: paid. Has a Door code.
- **Cancelled**: the customer cancelled a Confirmed booking in time and was refunded.
- **Expired**: payment never finished, or the customer backed out of paying, so the slot was released.

## Hold

The period a Held booking blocks its slot while the customer pays. Once the Hold runs out, the slot is open again even if the Booking has not yet been marked Expired.

## Players

The number of people declared at booking, including the booker. Every player is declared up front, and the price depends on it.

## Booking window

How far ahead a Booking may start: from now until the end of the 14th day, counting today.

## Door code

A six-digit code issued when a Booking is Confirmed. It opens the studio door only during the Access window.

## Access window

When a Door code works: from 10 minutes before the Booking starts until it ends.

## Grid status

What the public booking grid shows for a Booking, worked out from the clock and never from customer details: **Booked** (not started), **In Progress** (under way) or **Complete** (finished).
