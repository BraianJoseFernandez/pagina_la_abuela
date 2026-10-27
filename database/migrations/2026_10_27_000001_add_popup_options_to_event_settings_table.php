<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Opciones del modal automático del evento / flyer al entrar a la carta.
     */
    public function up(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('event_settings', 'auto_popup')) {
                $table->boolean('auto_popup')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('event_settings', 'popup_frequency')) {
                // always = cada vez que entra | session = una vez por visita | daily = una vez por día
                $table->string('popup_frequency', 20)->default('always')->after('auto_popup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            if (Schema::hasColumn('event_settings', 'popup_frequency')) {
                $table->dropColumn('popup_frequency');
            }
            if (Schema::hasColumn('event_settings', 'auto_popup')) {
                $table->dropColumn('auto_popup');
            }
        });
    }
};
