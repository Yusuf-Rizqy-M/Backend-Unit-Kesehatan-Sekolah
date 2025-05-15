<?php

namespace App\Http\Controllers\API;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    // Menampilkan semua department
    public function index()
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    // Menampilkan detail department berdasarkan ID
    public function show($id)
    {
        $department = Department::with('grades')->find($id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found'
            ], 404);
        }

        return response()->json($department);
    }


    // Membuat department baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::create($request->only(['name', 'desc']));

        return response()->json([
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    // Mengupdate department
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $department->update($request->only(['name', 'desc']));

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    // Menghapus department
    // Menonaktifkan department (soft delete)
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        // Cek apakah department memiliki grades terkait
        if ($department->grades()->count() > 0) {
            return response()->json([
                'message' => 'Cannot deactivate department with associated grades'
            ], 422);
        }

        // Ubah status menjadi inactive
        $department->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Department deactivated successfully'
        ]);
    }
}