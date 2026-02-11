<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('lead_seguimientos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('lead_id')
              ->constrained()
              ->onDelete('cascade');

        $table->string('estado'); // Prospecto, Prospecto frío, etc.
        $table->date('fecha')->nullable();
        $table->time('hora')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_seguimientos');
    }
};
