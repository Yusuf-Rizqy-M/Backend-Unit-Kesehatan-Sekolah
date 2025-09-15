<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    // Ambil semua data inventaris
    public function index()
    {
        $inventaris = Inventaris::all();
        return response()->json($inventaris);
    }

    // Tambah data inventaris
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'required|string|max:100',
            'status' => 'in:active,inactive'
        ]);

        $inventaris = Inventaris::create([
            'nama_barang' => $validated['nama_barang'],
            'jumlah' => $validated['jumlah'],
            'kondisi' => $validated['kondisi'],
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'Data inventaris berhasil ditambahkan',
            'data' => $inventaris,
        ], 201);
    }

    // Tampilkan data inventaris berdasarkan ID
    public function show($id)
    {
        $inventaris = Inventaris::findOrFail($id);
        return response()->json($inventaris);
    }

    // Update data inventaris
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_barang' => 'sometimes|string|max:255',
            'jumlah' => 'sometimes|integer|min:1',
            'kondisi' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $inventaris = Inventaris::findOrFail($id);
        $inventaris->update($validated);

        return response()->json([
            'message' => 'Data inventaris berhasil diperbarui',
            'data' => $inventaris,
        ]);
    }

    // Hapus data inventaris
    public function destroy($id)
    {
        $inventaris = Inventaris::findOrFail($id);
        $inventaris->delete();

        return response()->json([
            'message' => 'Data inventaris berhasil dihapus',
        ]);
    }

    // Ubah status active/inactive
    public function updateStatus($id)
    {
        $inventaris = Inventaris::findOrFail($id);

        $inventaris->status = $inventaris->status === 'active' ? 'inactive' : 'active';
        $inventaris->save();

        return response()->json([
            'message' => 'Status inventaris berhasil diubah',
            'status' => $inventaris->status,
        ]);
    }
}
