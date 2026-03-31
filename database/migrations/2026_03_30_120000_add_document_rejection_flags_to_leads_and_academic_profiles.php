<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('doc_acta_rechazado')->default(false);
            $table->boolean('doc_certificado_rechazado')->default(false);
            $table->boolean('doc_curp_rechazado')->default(false);
            $table->boolean('doc_ine_rechazado')->default(false);
        });

        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->boolean('doc_acta_rechazado')->default(false);
            $table->boolean('doc_certificado_rechazado')->default(false);
            $table->boolean('doc_curp_rechazado')->default(false);
            $table->boolean('doc_ine_rechazado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'doc_acta_rechazado',
                'doc_certificado_rechazado',
                'doc_curp_rechazado',
                'doc_ine_rechazado',
            ]);
        });

        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'doc_acta_rechazado',
                'doc_certificado_rechazado',
                'doc_curp_rechazado',
                'doc_ine_rechazado',
            ]);
        });
    }
};
