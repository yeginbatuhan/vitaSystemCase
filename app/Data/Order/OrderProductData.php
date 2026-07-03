<?php

namespace App\Data\Order;

use App\Data\Product\ProductData;
use App\Models\OrderProduct;
use Spatie\LaravelData\Data;

class OrderProductData extends Data
{
    public function __construct(
        public string $uuid,
        public ProductData $product,
        public int $quantity,
        public float $discount,
        public float $price,
        public float $total_price,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }

    public static function fromModel(OrderProduct $orderProduct): self
    {
        return new self(
            uuid: $orderProduct->uuid,
            product: ProductData::fromModel($orderProduct->product),
            quantity: $orderProduct->quantity,
            discount: (float) $orderProduct->discount,
            price: (float) $orderProduct->price,
            total_price: (float) $orderProduct->total_price,
            created_at: $orderProduct->created_at?->toISOString(),
            updated_at: $orderProduct->updated_at?->toISOString(),
        );
    }
}
