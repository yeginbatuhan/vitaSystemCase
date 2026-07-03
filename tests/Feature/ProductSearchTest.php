<?php

use App\Models\Product;
use App\Models\User;

it('3 karakterden kısa aramada 422 döner', function () {
    $user = User::factory()->create();

    $this->getJson('/api/products?search=ab', authHeaders($user))
        ->assertStatus(422);
});

it('name üzerinden eşleşen ürünleri döner', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'KONTAKTOR 4KW 9A 220V', 'code' => 'LC1D09M7']);
    Product::factory()->create(['name' => 'SIGORTA 1A', 'code' => '5SL4101']);

    $this->getJson('/api/products?search=KONTAKTOR', authHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'LC1D09M7')
        ->assertJsonStructure(['data' => [['uuid', 'name', 'code', 'price', 'manufacturer' => ['uuid', 'name']]]]);
});

it('code üzerinden eşleşen ürünleri döner', function () {
    $user = User::factory()->create();
    Product::factory()->create(['name' => 'KONTAKTOR', 'code' => 'LC1D09M7']);

    $this->getJson('/api/products?search=LC1D', authHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'LC1D09M7');
});
