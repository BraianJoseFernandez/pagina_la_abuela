<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_cooking_options')->default(false)->after('badge');
            $table->json('cooking_options')->nullable()->after('has_cooking_options');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('cooking_method')->nullable()->after('variant_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_cooking_options', 'cooking_options']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cooking_method');
        });
    }
};
