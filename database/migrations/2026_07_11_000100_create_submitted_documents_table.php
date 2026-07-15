<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submitted_documents', function (Blueprint $table) {
            $table->id();
            // Documento configurado (Ajustes → Expediente) que este archivo satisface.
            $table->foreignId('document_requirement_id')->constrained('document_requirements')->cascadeOnDelete();
            // Usuario dueño del documento (aspirante / alumno).
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('archivo_path');
            $table->string('nombre_original')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'document_requirement_id'], 'submitted_docs_user_req_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submitted_documents');
    }
};
