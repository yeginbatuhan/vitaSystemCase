<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginAction;
use App\Data\Auth\LoginData;
use App\Data\Auth\TokenData;
use App\Data\User\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginAction $action): TokenData
    {
        return $action(LoginData::from($request->validated()));
    }

    public function me(): UserData
    {
        $user = Auth::guard('api')->user();

        return UserData::fromModel($user);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => true,
            'message' => 'Oturum kapatıldı.',
        ]);
    }
}
