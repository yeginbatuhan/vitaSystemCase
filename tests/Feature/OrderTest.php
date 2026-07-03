<?php

use App\Models\Product;
use App\Models\User;

it('sipariş oluşturur ve toplamları doğru hesaplar', function () {
    $user = User::factory()->create();
    $first = Product::factory()->create(['price' => 100]);
    $second = Product::factory()->create(['price' => 200]);

    $response = $this->postJson('/api/orders', [
        'products' => [
            ['product_uuid' => $first->uuid, 'quantity' => 2, 'discount' => 10],
            ['product_uuid' => $second->uuid, 'quantity' => 1, 'discount' => 0],
        ],
    ], authHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonCount(2, 'products');

    expect($response->json('total_price'))->toEqual(380.0);
    expect($response->json('products.0.total_price'))->toEqual(180.0);
    expect($response->json('products.0.price'))->toEqual(100.0);
    expect($response->json('products.1.total_price'))->toEqual(200.0);
    expect($response->json('reference_no'))->toHaveLength(10);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_price' => 380.0,
    ]);
    $this->assertDatabaseCount('order_products', 2);
});

it('token olmadan sipariş oluşturulamaz', function () {
    $product = Product::factory()->create();

    $this->postJson('/api/orders', [
        'products' => [
            ['product_uuid' => $product->uuid, 'quantity' => 1, 'discount' => 0],
        ],
    ])->assertUnauthorized();
});

it('geçersiz indirimde 422 döner', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->postJson('/api/orders', [
        'products' => [
            ['product_uuid' => $product->uuid, 'quantity' => 1, 'discount' => 150],
        ],
    ], authHeaders($user))->assertStatus(422);
});

it('olmayan ürün uuidsinde 422 döner', function () {
    $user = User::factory()->create();

    $this->postJson('/api/orders', [
        'products' => [
            ['product_uuid' => '11111111-1111-1111-1111-111111111111', 'quantity' => 1, 'discount' => 0],
        ],
    ], authHeaders($user))->assertStatus(422);
});

it('boş ürün listesinde 422 döner', function () {
    $user = User::factory()->create();

    $this->postJson('/api/orders', ['products' => []], authHeaders($user))
        ->assertStatus(422);
});

it('kullanıcının siparişlerini listeler', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50]);

    $this->postJson('/api/orders', [
        'products' => [
            ['product_uuid' => $product->uuid, 'quantity' => 1, 'discount' => 0],
        ],
    ], authHeaders($user))->assertCreated();

    $list = $this->getJson('/api/orders', authHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');

    expect($list->json('data.0.total_price'))->toEqual(50.0);
});
