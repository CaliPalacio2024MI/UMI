<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulario_campos', function (Blueprint $table) {
            $table->boolean('valoracion')->default(false)->after('obligatorio');
            $table->boolean('es_nombre')->default(false)->after('valoracion');
            $table->boolean('demografica')->default(false)->after('es_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('formulario_campos', function (Blueprint $table) {
            $table->dropColumn(['valoracion', 'es_nombre', 'demografica']);
        });
    }
};
