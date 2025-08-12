<?php

namespace App\Http\Controllers\API;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    // Menampilkan semua department dengan status active
    public function index()
    {
        $departments = Department::where('status', 'active')
                                ->get()
                                ->map(function ($department) {
                                    return [
                                        'id' => $department->id,
                                        'name' => $department->name,
                                        'desc' => $department->desc,
                                        'status' => $department->status, // Tambahkan kolom status
                                    ];
                                });

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

        return response()->json([
            'id' => $department->id,
            'name' => $department->name,
            'desc' => $department->desc,
            'status' => $department->status, // Tambahkan status dalam respons
            'grades' => $department->grades // Tetap sertakan grades jika ada
        ]);
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

        $department = Department::create(array_merge(
            $request->only(['name', 'desc']),
            ['status' => 'active'] // Set status default ke 'active'
        ));

        return response()->json([
            'message' => 'Department created successfully',
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'desc' => $department->desc,
                'status' => $department->status // Tambahkan status dalam respons
            ]
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

        $department->update(array_merge(
            $request->only(['name', 'desc']),
            ['status' => 'active'] // Pastikan status tetap 'active' saat update
        ));

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'desc' => $department->desc,
                'status' => $department->status // Tambahkan status dalam respons
            ]
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