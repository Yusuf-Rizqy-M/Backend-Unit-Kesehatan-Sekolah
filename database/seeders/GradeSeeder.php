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

        // PPLG: 10-12 PPLG 1-2
        foreach (['10', '11', '12'] as $class) {
            foreach (range(1, 2) as $num) {
                Grade::create([
                    'name' => "{$class} RPL {$num}",
                    'class' => (int) $class,
                    'department_id' => $departments['RPL']->id,
                ]);
            }
        }

        // Animasi 3D: 10-12 Animasi 3D 1-3
        foreach (['10', '11', '12'] as $class) {
            foreach (range(1, 3) as $num) {
                Grade::create([
                    'name' => "{$class} Animasi 3D {$num}",
                    'class' => (int) $class,
                    'department_id' => $departments['Animasi 3D']->id,
                ]);
            }
        }

        // Animasi 2D: 10-12 Animasi 2D 4-5
        foreach (['10', '11', '12'] as $class) {
            foreach ([4, 5] as $num) {
                Grade::create([
                    'name' => "{$class} Animasi 2D {$num}",
                    'class' => (int) $class,
                    'department_id' => $departments['Animasi 2D']->id,
                ]);
            }
        }

        // DKV DG: 10-12 DKV DG 1-2
        foreach (['10', '11', '12'] as $class) {
            foreach (range(1, 2) as $num) {
                Grade::create([
                    'name' => "{$class} DKV DG {$num}",
                    'class' => (int) $class,
                    'department_id' => $departments['DKV DG']->id,
                ]);
            }
        }

        // DKV TG: 10-12 DKV TG 3-5
        foreach (['10', '11', '12'] as $class) {
            foreach (range(3, 5) as $num) {
                Grade::create([
                    'name' => "{$class} DKV TG {$num}",
                    'class' => (int) $class,
                    'department_id' => $departments['DKV TG']->id,
                ]);
            }
        }
    }
}
