<?php

namespace App\Http\Controllers\API;

use App\Models\Grade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GradeController extends Controller
{
    // Menampilkan semua grade
    public function index()
    {
        $grades = Grade::with('department')->get()->map(function ($grade) {
            return [
                'id' => $grade->id,
                'name' => $grade->name,
                'department_name' => $grade->department->name,
            ];
        });
    
        return response()->json($grades);
    }
    
}
