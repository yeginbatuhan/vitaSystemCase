<?php

namespace App\Data\User;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public string $uuid,
        public string $full_name,
        public string $email,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            uuid: $user->uuid,
            full_name: $user->name,
            email: $user->email,
        );
    }
}
