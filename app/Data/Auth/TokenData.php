<?php

namespace App\Data\Auth;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Symfony\Component\HttpFoundation\Response;

class TokenData extends Data
{
    public function __construct(
        public string $token,
        public string $token_type,
        public int $expires_in,
    ) {
    }

    protected function calculateResponseStatus(Request $request): int
    {
        return Response::HTTP_OK;
    }
}
