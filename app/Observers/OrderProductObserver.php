<?php

namespace App\Observers;

use App\Models\OrderProduct;
use Illuminate\Support\Str;

class OrderProductObserver
{
    public function creating(OrderProduct $orderProduct): void
    {
        if (empty($orderProduct->uuid)) {
            $orderProduct->uuid = (string) Str::uuid();
        }
    }
}
