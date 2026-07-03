<?php

namespace App\Data\Order;

use Spatie\LaravelData\Data;

class OrderLineData extends Data
{
    public function __construct(
        public string $product_uuid,
        public int $quantity,
        public float $discount,
    ) {
    }
}
