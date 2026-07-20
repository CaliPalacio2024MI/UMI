<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submitted_documents', function (Blueprint $table) {
            // null = sin revisar, 'aceptado', 'rechazado'
            $table->string('validation_status', 20)->nullable()->after('uploaded_by');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete()->after('validation_status');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('submitted_documents', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['validation_status', 'validated_by', 'validated_at']);
        });
    }
};
