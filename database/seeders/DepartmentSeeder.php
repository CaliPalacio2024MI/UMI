<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {

        $institutionId = 1;

        Department::insert([
            [
                'name' => 'Recursos Humanos',
                'institution_id' => $institutionId
            ],
            [
                'name' => 'Tecnologías de la Información',
                'institution_id' => $institutionId
            ],
            [
                'name' => 'Finanzas',
                'institution_id' => $institutionId
            ],
        ]);
    }
}
