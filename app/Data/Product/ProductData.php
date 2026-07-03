<?php

namespace App\Data\Product;

use App\Data\Manufacturer\ManufacturerData;
use App\Models\Product;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $code,
        public float $price,
        public ManufacturerData $manufacturer,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            uuid: $product->uuid,
            name: $product->name,
            code: $product->code,
            price: (float) $product->price,
            manufacturer: ManufacturerData::fromModel($product->manufacturer),
        );
    }
}
