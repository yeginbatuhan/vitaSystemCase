<?php

namespace App\Data\Manufacturer;

use App\Models\Manufacturer;
use Spatie\LaravelData\Data;

class ManufacturerData extends Data
{
    public function __construct(
        public string $uuid,
        public string $name,
    ) {
    }

    public static function fromModel(Manufacturer $manufacturer): self
    {
        return new self(
            uuid: $manufacturer->uuid,
            name: $manufacturer->name,
        );
    }
}
