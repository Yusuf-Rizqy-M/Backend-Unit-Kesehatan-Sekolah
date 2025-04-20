<?php

namespace App\Http\Controllers\API;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    // Menampilkan semua department
    public function index()
    {
        $departments = Department::all();
        return response()->json($departments);
    }
    public function grades($id)
    {
        $department = Department::with('grades')->findOrFail($id);
        return response()->json($department->grades);
    }

}
