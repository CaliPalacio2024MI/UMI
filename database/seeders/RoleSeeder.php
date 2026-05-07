<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'master'],
            ['display_name' => 'Master']
        );

        Role::firstOrCreate(
            ['name' => 'gerente_capacitacion'],
            ['display_name' => 'Gerente de Capacitación']
        );

        Role::firstOrCreate(
            ['name' => 'gerente_th'],
            ['display_name' => 'Gerente de Talento Humano']
        );

        Role::firstOrCreate(
            ['name' => 'anfitrion'],
            ['display_name' => 'Anfitrión']
        );

        Role::firstOrCreate(
            ['name' => 'control_administrativo'],
            ['display_name' => 'Control Administrativo']
        );

        Role::firstOrCreate(
            ['name' => 'docente'],
            ['display_name' => 'Docente']
        );

        Role::updateOrCreate(
            ['name' => 'estudiante'],
            ['display_name' => 'Alumno']
        );

        Role::firstOrCreate(
            ['name' => 'ctp'],
            ['display_name' => 'CTP']
        );
        
        Role::firstOrCreate(
            ['name' => 'coordinador_ctp'],
            ['display_name' => 'Coordinador del CTP']
        );
        

    }
}