<?php

namespace App\Data\Order;

use App\Data\User\UserData;
use App\Models\Order;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        public string $uuid,
        public string $reference_no,
        public UserData $user,
        public float $total_price,
        #[DataCollectionOf(OrderProductData::class)]
        public array $products,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            uuid: $order->uuid,
            reference_no: $order->reference_no,
            user: UserData::fromModel($order->user),
            total_price: (float) $order->total_price,
            products: $order->products->map(
                fn ($orderProduct) => OrderProductData::fromModel($orderProduct)
            )->all(),
            created_at: $order->created_at?->toISOString(),
            updated_at: $order->updated_at?->toISOString(),
        );
    }
}
