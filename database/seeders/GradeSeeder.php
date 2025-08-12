<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Department;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run()
    {
        $departments = Department::all()->keyBy('name');

        // PPLG (RPL 1-2)
        Grade::create(['name' => 'RPL 1', 'department_id' => $departments['RPL']->id]);
        Grade::create(['name' => 'RPL 2', 'department_id' => $departments['RPL']->id]);
        Grade::create(['name' => 'RPL 3', 'department_id' => $departments['RPL']->id]);

        // Animasi 3D (1-3)
        Grade::create(['name' => 'Animasi 3D 1', 'department_id' => $departments['Animasi 3D']->id]);
        Grade::create(['name' => 'Animasi 3D 2', 'department_id' => $departments['Animasi 3D']->id]);
        Grade::create(['name' => 'Animasi 3D 3', 'department_id' => $departments['Animasi 3D']->id]);

        // Animasi 2D (4-5)
        Grade::create(['name' => 'Animasi 2D', 'department_id' => $departments['Animasi 2D']->id]);
        Grade::create(['name' => 'Animasi 2D', 'department_id' => $departments['Animasi 2D']->id]);


        // DKV DG (1-2)
        Grade::create(['name' => 'DKV DG 1', 'department_id' => $departments['DKV DG']->id]);
        Grade::create(['name' => 'DKV DG 2', 'department_id' => $departments['DKV DG']->id]);
        // DKV TG (3-5)
        Grade::create(['name' => 'DKV TG 3', 'department_id' => $departments['DKV TG']->id]);
        Grade::create(['name' => 'DKV TG 4', 'department_id' => $departments['DKV TG']->id]);
        Grade::create(['name' => 'DKV TG 5', 'department_id' => $departments['DKV TG']->id]);
    }
}
