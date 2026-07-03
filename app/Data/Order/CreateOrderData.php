<?php

namespace App\Data\Order;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        #[DataCollectionOf(OrderLineData::class)]
        public array $products,
    ) {
    }
}
