<?php

namespace App\Actions\Auth;

use App\Data\Auth\LoginData;
use App\Data\Auth\TokenData;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Auth;

class LoginAction
{
    public function __invoke(LoginData $data): TokenData
    {
        $token = Auth::guard('api')->attempt([
            'email' => $data->email,
            'password' => $data->password,
        ]);

        if ($token === false) {
            throw new InvalidCredentialsException();
        }

        return new TokenData(
            token: $token,
            token_type: 'bearer',
            expires_in: Auth::guard('api')->factory()->getTTL() * 60,
        );
    }
}
