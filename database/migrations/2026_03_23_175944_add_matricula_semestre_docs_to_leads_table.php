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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('doc_acta_nacimiento')->nullable()->after('clasificacion');
            $table->string('doc_certificado_prepa')->nullable()->after('doc_acta_nacimiento');
            $table->string('doc_curp')->nullable()->after('doc_certificado_prepa');
            $table->string('doc_ine')->nullable()->after('doc_curp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine']);
        });
    }
};
