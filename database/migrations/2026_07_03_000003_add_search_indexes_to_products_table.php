<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS products_name_index ON products (name)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_code_index ON products (code)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_name_index');
        DB::statement('DROP INDEX IF EXISTS products_code_index');
    }
};
