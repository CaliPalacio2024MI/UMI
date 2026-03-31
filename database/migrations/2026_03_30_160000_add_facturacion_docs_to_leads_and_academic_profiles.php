<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('doc_ficha_pago')->nullable();
            $table->string('doc_factura_xml')->nullable();
            $table->boolean('doc_ficha_pago_rechazado')->default(false);
            $table->boolean('doc_factura_xml_rechazado')->default(false);
        });

        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->string('doc_ficha_pago')->nullable();
            $table->string('doc_factura_xml')->nullable();
            $table->boolean('doc_ficha_pago_rechazado')->default(false);
            $table->boolean('doc_factura_xml_rechazado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'doc_ficha_pago',
                'doc_factura_xml',
                'doc_ficha_pago_rechazado',
                'doc_factura_xml_rechazado',
            ]);
        });

        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'doc_ficha_pago',
                'doc_factura_xml',
                'doc_ficha_pago_rechazado',
                'doc_factura_xml_rechazado',
            ]);
        });
    }
};
