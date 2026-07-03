<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('valid kimlik bilgileriyle token döner', function () {
    User::factory()->create([
        'email' => 'john@doe.com',
        'password' => Hash::make('vita123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'john@doe.com',
        'password' => 'vita123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'expires_in']);
});

it('hatalı şifrede 401 döner', function () {
    User::factory()->create([
        'email' => 'john@doe.com',
        'password' => Hash::make('vita123'),
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'john@doe.com',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

it('token olmadan korumalı uca 401 döner', function () {
    $this->getJson('/api/products?search=abc')->assertUnauthorized();
});

it('me ucu giriş yapmış kullanıcıyı döner', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    $this->getJson('/api/auth/me', authHeaders($user))
        ->assertOk()
        ->assertJsonPath('full_name', 'John Doe')
        ->assertJsonPath('email', $user->email);
});
