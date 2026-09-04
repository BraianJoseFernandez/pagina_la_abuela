<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('product_id');
        });

        // Poblar categoría para los ítems existentes en la base de datos
        try {
            DB::statement("
                UPDATE order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                SET oi.category_name = c.name
                WHERE oi.category_name IS NULL
            ");

            DB::statement("
                UPDATE order_items oi
                JOIN products p ON oi.product_name = p.name
                JOIN categories c ON p.category_id = c.id
                SET oi.category_name = c.name
                WHERE oi.category_name IS NULL
            ");
        } catch (\Throwable $e) {
            // Continuar si la consulta directa no aplica
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }
};
