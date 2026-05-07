<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Asegura que los roles CTP y Coordinador del CTP existan en la tabla roles.
     */
    public function up(): void
    {
        $roles = [
            ['name' => 'ctp', 'display_name' => 'CTP'],
            ['name' => 'coordinador_ctp', 'display_name' => 'Coordinador del CTP'],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')->where('name', $role['name'])->exists();
            if (!$exists) {
                DB::table('roles')->insert(array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->whereIn('name', ['ctp', 'coordinador_ctp'])->delete();
    }
};
