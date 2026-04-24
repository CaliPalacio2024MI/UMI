<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            if (! Schema::hasColumn('careers', 'fecha_vencimiento_moratorio')) {
                $table->date('fecha_vencimiento_moratorio')->nullable()->after('cargo_monetario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            if (Schema::hasColumn('careers', 'fecha_vencimiento_moratorio')) {
                $table->dropColumn('fecha_vencimiento_moratorio');
            }
        });
    }
};
