<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(int $userId): Order;

    public function attachProduct(Order $order, array $attributes): OrderProduct;

    public function save(Order $order): void;

    public function loadRelations(Order $order): Order;

    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;
}
