<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ObatController extends Controller
{
    // Get all obat
    public function index()
    {
        $obat = Obat::where('status', 'active')->get()->map(function ($obat) {
            if ($obat->gambar) {
                $obat->gambar = url(Storage::url($obat->gambar));
            }
            return $obat;
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar semua obat aktif',
            'data' => $obat
        ], 200);
    }

    // Show obat
    public function show($id)
    {
        $obat = Obat::where('status', 'active')->find($id);

        if (!$obat) {
            return response()->json([
                'status' => false,
                'message' => 'Obat tidak ditemukan atau sudah nonaktif',
                'data' => null
            ], 404);
        }

        if ($obat->gambar) {
            $obat->gambar = url(Storage::url($obat->gambar));
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail obat',
            'data' => $obat
        ], 200);
    }

    // Create obat
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_obat' => 'required|string|max:255',
                'stok' => 'required|integer',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'tanggal_kadaluarsa' => 'nullable|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'data' => null
            ], 422);
        }

        $data = [
            'nama_obat' => $request->nama_obat,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'kategori' => $request->kategori,
            'status' => 'active'
        ];

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('obat', 'public');
        }

        $obat = Obat::create($data);

        if ($obat->gambar) {
            $obat->gambar = url(Storage::url($obat->gambar));
        }

        return response()->json([
            'status' => true,
            'message' => 'Obat berhasil ditambahkan',
            'data' => $obat
        ], 201);
    }

    // Update obat
    public function update(Request $request, $id)
    {
        $obat = Obat::where('status', 'active')->find($id);

        if (!$obat) {
            return response()->json([
                'status' => false,
                'message' => 'Obat tidak ditemukan atau sudah nonaktif',
                'data' => null
            ], 404);
        }

        try {
            $request->validate([
                'nama_obat' => 'sometimes|string|max:255',
                'stok' => 'sometimes|integer',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'tanggal_kadaluarsa' => 'nullable|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'data' => null
            ], 422);
        }

        $data = $request->only('nama_obat', 'stok', 'deskripsi', 'tanggal_kadaluarsa', 'kategori');

        if ($request->hasFile('gambar')) {
            if ($obat->gambar) {
                Storage::disk('public')->delete($obat->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('obat', 'public');
        }

        $obat->update($data);

        if ($obat->gambar) {
            $obat->gambar = url(Storage::url($obat->gambar));
        }

        return response()->json([
            'status' => true,
            'message' => 'Obat berhasil diperbarui',
            'data' => $obat
        ], 200);
    }

    // Soft delete obat (ubah status)
    public function destroy($id)
    {
        $obat = Obat::find($id);

        if (!$obat || $obat->status === 'inactive') {
            return response()->json([
                'status' => false,
                'message' => 'Obat tidak ditemukan atau sudah nonaktif',
                'data' => null
            ], 404);
        }

        $obat->status = 'inactive';
        $obat->save();

        return response()->json([
            'status' => true,
            'message' => 'Obat berhasil dinonaktifkan',
            'data' => $obat
        ], 200);
    }

    // Tambah stok
    public function tambahStok(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1'
        ]);

        $obat = Obat::where('status', 'active')->find($id);

        if (!$obat) {
            return response()->json([
                'status' => false,
                'message' => 'Obat tidak ditemukan atau sudah nonaktif',
                'data' => null
            ], 404);
        }

        $obat->stok += $request->jumlah;
        $obat->save();

        return response()->json([
            'status' => true,
            'message' => 'Stok berhasil ditambahkan',
            'data' => $obat
        ], 200);
    }

    // Kurangi stok
    public function kurangiStok(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1'
        ]);

        $obat = Obat::where('status', 'active')->find($id);

        if (!$obat) {
            return response()->json([
                'status' => false,
                'message' => 'Obat tidak ditemukan atau sudah nonaktif',
                'data' => null
            ], 404);
        }

        if ($obat->stok < $request->jumlah) {
            return response()->json([
                'status' => false,
                'message' => 'Stok tidak mencukupi',
                'data' => $obat
            ], 400);
        }

        $obat->stok -= $request->jumlah;
        $obat->save();

        return response()->json([
            'status' => true,
            'message' => 'Stok berhasil dikurangi',
            'data' => $obat
        ], 200);
    }
}
