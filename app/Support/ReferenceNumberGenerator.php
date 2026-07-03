<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Str;

class ReferenceNumberGenerator
{
    private const LENGTH = 10;

    public function generate(): string
    {
        do {
            $reference = Str::upper(Str::random(self::LENGTH));
        } while (Order::withTrashed()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
