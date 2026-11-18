<?php

namespace App\Payments;

use RuntimeException;

/** The payment provider could not complete a request. */
final class PaymentFailed extends RuntimeException {}
