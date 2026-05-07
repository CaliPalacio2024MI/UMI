<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('career_user')) {
            Schema::create('career_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('career_id')->constrained('careers')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'career_id']);
            });
        }

        if (Schema::hasTable('academic_profiles')) {
            $rows = DB::table('academic_profiles')
                ->whereNotNull('career_id')
                ->select('user_id', 'career_id')
                ->get();
            foreach ($rows as $row) {
                $payload = [
                    'user_id' => $row->user_id,
                    'career_id' => $row->career_id,
                ];
                if (Schema::hasColumn('career_user', 'created_at')) {
                    $t = now();
                    $payload['created_at'] = $t;
                    $payload['updated_at'] = $t;
                }
                DB::table('career_user')->insertOrIgnore($payload);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user');
    }
};
