<?php

use App\Support\BookingPrice;

it('charges the bay rate plus each extra player, then tax', function () {
    // 2 hours × (35 + 8 × 2) = $102.00, plus 5% = $5.10
    $price = BookingPrice::for(120, 3);

    expect($price->hourlyCents)->toBe(5100)
        ->and($price->subtotalCents)->toBe(10200)
        ->and($price->taxCents)->toBe(510)
        ->and($price->totalCents)->toBe(10710);
});

it('prices half hours', function () {
    // 1.5 hours × $35 = $52.50, tax $2.63 (rounded)
    $price = BookingPrice::for(90, 1);

    expect($price->subtotalCents)->toBe(5250)
        ->and($price->taxCents)->toBe(263)
        ->and($price->totalCents)->toBe(5513);
});
