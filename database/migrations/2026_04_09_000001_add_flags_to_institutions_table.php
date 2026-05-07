<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->boolean('is_administrativo')->default(false)->after('logo_path');
            $table->boolean('is_universidad')->default(false)->after('is_administrativo');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['is_administrativo', 'is_universidad']);
        });
    }
};
