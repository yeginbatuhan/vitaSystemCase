<?php

namespace App\Actions\Orders;

use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListOrdersAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {
    }

    public function __invoke(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->orders->paginateForUser($user, $perPage);
    }
}
