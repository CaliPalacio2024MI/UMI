<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_concepts', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_concepts', 'porcentaje_cargo_moratorio')) {
                $table->decimal('porcentaje_cargo_moratorio', 5, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('billing_concepts', 'cargo_monetario')) {
                $table->decimal('cargo_monetario', 12, 2)->nullable()->after('porcentaje_cargo_moratorio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_concepts', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('billing_concepts', 'porcentaje_cargo_moratorio')) {
                $drop[] = 'porcentaje_cargo_moratorio';
            }
            if (Schema::hasColumn('billing_concepts', 'cargo_monetario')) {
                $drop[] = 'cargo_monetario';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
