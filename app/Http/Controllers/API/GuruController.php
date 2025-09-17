<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Guru;

class GuruController extends Controller
{
    /**
     * Menampilkan semua guru.
     * Hanya dapat diakses oleh admin.
     */
    public function index()
    {
        $gurus = Guru::all();
        return response()->json([
            'status' => true,
            'message' => 'All gurus retrieved successfully',
            'data' => $gurus
        ], 200);
    }

    /**
     * Menampilkan detail guru berdasarkan ID.
     * Hanya dapat diakses oleh admin.
     * Contoh: /api/gurus/5
     */
    public function show($id)
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'status' => false,
                'message' => 'Guru not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $guru
        ], 200);
    }

    /**
     * Menambahkan guru baru.
     * Hanya dapat diakses oleh admin.
     * Contoh: POST /api/gurus
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'           => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'required|email|unique:gurus,email',
            'jenis_kelamin'  => 'required|in:Laki-laki,Perempuan',
            'mata_pelajaran' => 'required|string|max:100',
            'alamat'         => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $guru = Guru::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Guru created successfully',
            'data' => $guru
        ], 201);
    }

    /**
     * Mengupdate data guru berdasarkan ID.
     * Contoh: PUT /api/gurus/{id}
     */
    public function update(Request $request, $id)
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'status' => false,
                'message' => 'Guru not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'           => 'sometimes|string|max:255',
            'no_hp'          => 'sometimes|string|max:20',
            'email'          => 'sometimes|email|unique:gurus,email,' . $guru->id,
            'jenis_kelamin'  => 'sometimes|in:Laki-laki,Perempuan',
            'mata_pelajaran' => 'sometimes|string|max:100',
            'alamat'         => 'nullable|string',
            'status'         => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $guru->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Guru updated successfully',
            'data' => $guru
        ], 200);
    }

    /**
     * Mengubah status guru menjadi inactive (soft delete).
     * Contoh: DELETE /api/gurus/{id}
     */
    public function destroy($id)
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'status' => false,
                'message' => 'Guru not found'
            ], 404);
        }

        $guru->status = 'inactive';
        $guru->save();

        return response()->json([
            'status' => true,
            'message' => 'Guru deactivated successfully',
            'data' => $guru
        ], 200);
    }

    /**
     * Hitung total guru.
     * Contoh: GET /api/gurus/count
     */
    public function totalGuru()
    {
        $total = Guru::count();

        return response()->json([
            'status' => true,
            'message' => 'Total gurus retrieved successfully',
            'total' => $total
        ], 200);
    }
}
