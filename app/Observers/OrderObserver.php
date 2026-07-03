<?php

namespace App\Observers;

use App\Models\Order;
use App\Support\ReferenceNumberGenerator;
use Illuminate\Support\Str;

class OrderObserver
{
    public function __construct(
        private readonly ReferenceNumberGenerator $referenceNumberGenerator,
    ) {
    }

    public function creating(Order $order): void
    {
        if (empty($order->uuid)) {
            $order->uuid = (string) Str::uuid();
        }

        if (empty($order->reference_no)) {
            $order->reference_no = $this->referenceNumberGenerator->generate();
        }
    }
}
