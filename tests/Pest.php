<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function authHeaders(User $user): array
{
    $token = auth('api')->login($user);

    return ['Authorization' => "Bearer {$token}"];
}
