<?php

namespace App\Actions\Orders;

use App\Data\Order\CreateOrderData;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductRepositoryInterface $products,
    ) {
    }

    public function __invoke(User $user, CreateOrderData $data): Order
    {
        $uuids = array_map(fn ($line) => $line->product_uuid, $data->products);
        $catalog = $this->products->findByUuids($uuids)->keyBy('uuid');

        return DB::transaction(function () use ($user, $data, $catalog) {
            $order = $this->orders->create($user->id);
            $total = 0.0;

            foreach ($data->products as $line) {
                $product = $catalog->get($line->product_uuid);
                $price = (float) $product->price;
                $lineTotal = round($price * (1 - ($line->discount / 100)) * $line->quantity, 2);

                $this->orders->attachProduct($order, [
                    'product_id' => $product->id,
                    'quantity' => $line->quantity,
                    'discount' => $line->discount,
                    'price' => $price,
                    'total_price' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $order->total_price = round($total, 2);
            $this->orders->save($order);

            return $this->orders->loadRelations($order);
        });
    }
}
