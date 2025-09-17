<?php

namespace App\Http\Controllers\API;

use App\Models\Inventaris;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
            'kategori'    => 'nullable|string|max:255',
            'jumlah'      => 'required|integer|min:1',
            'kondisi'     => 'required|in:baik,rusak ringan,rusak berat',
            'status'      => 'nullable|in:active,inactive',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'nama_barang.string'   => 'Nama barang harus berupa teks.',
            'jumlah.required'      => 'Jumlah wajib diisi.',
            'jumlah.integer'       => 'Jumlah harus berupa angka.',
            'jumlah.min'           => 'Jumlah minimal 1.',
            'kondisi.required'     => 'Kondisi wajib dipilih.',
            'kondisi.in'           => 'Kondisi hanya boleh: baik, rusak ringan, rusak berat.',
            'status.in'            => 'Status hanya boleh: active atau inactive.',
        ]);

        $inventaris = Inventaris::create([
            'nama_barang' => $validated['nama_barang'],
            'kategori'    => $validated['kategori'],
            'jumlah'      => $validated['jumlah'],
            'kondisi'     => $validated['kondisi'] ?? 'baik',
            'status'      => $validated['status'] ?? 'active',
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
            'kategori'    => 'sometimes|nullable|string|max:255',
            'jumlah'      => 'sometimes|integer|min:1',
            'kondisi'     => 'sometimes|in:baik,rusak ringan,rusak berat',
            'status'      => 'sometimes|in:active,inactive',
        ], [
            'nama_barang.string' => 'Nama barang harus berupa teks.',
            'jumlah.integer'     => 'Jumlah harus berupa angka.',
            'jumlah.min'         => 'Jumlah minimal 1.',
            'kondisi.in'         => 'Kondisi hanya boleh: baik, rusak ringan, rusak berat.',
            'status.in'          => 'Status hanya boleh: active atau inactive.',
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
            'status'  => $inventaris->status,
        ]);
    }
}
