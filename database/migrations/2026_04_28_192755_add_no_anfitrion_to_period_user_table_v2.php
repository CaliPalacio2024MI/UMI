<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_user', function (Blueprint $table) {
            // ✅ Agregar columna no_anfitrion
            $table->string('no_anfitrion', 50)->nullable()->after('user_id');
            
            // ✅ Hacer user_id nullable (ya que ahora usaremos no_anfitrion)
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        
        // ✅ Crear índice único separado para no_anfitrion
        Schema::table('period_user', function (Blueprint $table) {
            $table->index(['period_id', 'no_anfitrion', 'course_id'], 'idx_period_anfitrion');
        });
    }

    public function down(): void
    {
        Schema::table('period_user', function (Blueprint $table) {
            $table->dropIndex('idx_period_anfitrion');
            $table->dropColumn('no_anfitrion');
        });
    }
};