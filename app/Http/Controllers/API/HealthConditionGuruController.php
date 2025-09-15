<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthConditionGuruController extends Controller
{
    public function index()
    {
        // Hanya admin yang bisa lihat semua data
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = Guru::all();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'no_hp' => 'required|string',
            'email' => 'required|email|unique:gurus,email',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'mata_pelajaran' => 'required|string',
            'alamat' => 'nullable|string',
            'status' => 'in:aktif,non-aktif',
        ]);

        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $guru = Guru::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $guru
        ]);
    }

    public function show($id)
    {
        $guru = Guru::find($id);

        if (!$guru) {
            return response()->json([
                'status' => false,
                'message' => 'Data guru tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $guru
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'sometimes|required|string',
            'no_hp' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:gurus,email,' . $id,
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'mata_pelajaran' => 'sometimes|required|string',
            'alamat' => 'nullable|string',
            'status' => 'in:aktif,non-aktif',
        ]);

        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $guru = Guru::findOrFail($id);
        $guru->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $guru
        ]);
    }

    public function destroy($id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $guru = Guru::findOrFail($id);
        $guru->update(['status' => 'inactive']); // soft delete via status

        return response()->json([
            'status' => true,
            'message' => 'Guru dinonaktifkan'
        ]);
    }
}
