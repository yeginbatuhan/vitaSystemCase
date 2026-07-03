<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('manufacturers')) {
            Schema::create('manufacturers', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('code');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('code');
                $table->foreignId('manufacturer_id')->constrained();
                $table->decimal('price', 12, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');
        Schema::dropIfExists('manufacturers');
    }
};
