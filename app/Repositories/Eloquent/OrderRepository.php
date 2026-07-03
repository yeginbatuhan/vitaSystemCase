<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(int $userId): Order
    {
        return Order::query()->create([
            'user_id' => $userId,
            'total_price' => 0,
        ]);
    }

    public function attachProduct(Order $order, array $attributes): OrderProduct
    {
        return $order->products()->create($attributes);
    }

    public function save(Order $order): void
    {
        $order->save();
    }

    public function loadRelations(Order $order): Order
    {
        return $order->load(['user', 'products.product.manufacturer']);
    }

    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->orders()
            ->with(['user', 'products.product.manufacturer'])
            ->latest()
            ->paginate($perPage);
    }
}
