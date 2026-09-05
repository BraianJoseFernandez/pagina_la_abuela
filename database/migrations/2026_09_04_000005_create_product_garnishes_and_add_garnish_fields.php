<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna has_garnishes a products
        if (!Schema::hasColumn('products', 'has_garnishes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_garnishes')->default(false)->after('has_cooking_options');
            });
        }

        // 2. Crear tabla product_garnishes
        if (!Schema::hasTable('product_garnishes')) {
            Schema::create('product_garnishes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('name');
                $table->string('description', 500)->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('image_path')->nullable();
                $table->boolean('is_available')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // 3. Agregar columnas garnish_name y garnish_price a order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'garnish_name')) {
                $table->string('garnish_name')->nullable()->after('cooking_method');
            }
            if (!Schema::hasColumn('order_items', 'garnish_price')) {
                $table->decimal('garnish_price', 10, 2)->default(0.00)->after('garnish_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'garnish_price')) {
                $table->dropColumn('garnish_price');
            }
            if (Schema::hasColumn('order_items', 'garnish_name')) {
                $table->dropColumn('garnish_name');
            }
        });

        Schema::dropIfExists('product_garnishes');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'has_garnishes')) {
                $table->dropColumn('has_garnishes');
            }
        });
    }
};
