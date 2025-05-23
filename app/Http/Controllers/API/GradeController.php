<?php

namespace App\Http\Controllers\API;

use App\Models\Grade;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    // Membuat grade baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'class' => 'required|string|in:10,11,12',
            'department_id' => 'required|exists:departments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $grade = Grade::create($request->only(['name', 'class', 'department_id']));

        return response()->json([
            'message' => 'Grade created successfully',
            'data' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'class' => $grade->class,
                'department_id' => $grade->department_id
            ]
        ], 201);
    }

    // Mengupdate grade
    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'class' => 'required|string|in:10,11,12',
            'department_id' => 'required|exists:departments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $grade->update($request->only(['name', 'class', 'department_id']));

        return response()->json([
            'message' => 'Grade updated successfully',
            'data' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'class' => $grade->class,
                'department_id' => $grade->department_id
            ]
        ]);
    }

    // Menonaktifkan grade (soft delete)
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);

        // Ubah status menjadi inactive
        $grade->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Grade deactivated successfully'
        ]);
    }
    // Menampilkan detail grade berdasarkan ID
    public function show($id)
    {
        $grade = Grade::with('department')->find($id);

        if (!$grade) {
            return response()->json([
                'message' => 'Grade not found'
            ], 404);
        }

        return response()->json([
            'id' => $grade->id,
            'name' => $grade->name,
            'class' => $grade->class,
            'department' => [
                'id' => $grade->department->id,
                'name' => $grade->department->name
            ]
        ]);
    }

}