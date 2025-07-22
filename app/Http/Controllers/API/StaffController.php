<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::where('status', 'active')->get()->map(function ($staff) {
            if ($staff->image) {
                $staff->image = url(Storage::url($staff->image));
            }
            return $staff;
        });
        return response()->json($staff);
    }

    public function show($id)
    {
        $staff = Staff::where('status', 'active')->find($id);
        if (!$staff) {
            return response()->json(['message' => 'Staff tidak ditemukan atau tidak aktif'], 404);
        }
        if ($staff->image) {
            $staff->image = url(Storage::url($staff->image));
        }
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|string',
                'name' => 'required|string|unique:staff,name',
                'wa' => 'required|digits_between:8,15|numeric|unique:staff,wa',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'role.required' => 'Role wajib diisi.',
                'name.required' => 'Nama wajib diisi.',
                'name.unique' => 'Nama sudah digunakan.',
                'wa.required' => 'Nomor WhatsApp wajib diisi.',
                'wa.numeric' => 'Nomor WhatsApp hanya boleh berisi angka.',
                'wa.digits_between' => 'Nomor WhatsApp harus terdiri dari 8 hingga 15 digit.',
                'wa.unique' => 'Nomor WhatsApp sudah digunakan.',
                'image.image' => 'File harus berupa gambar.',
                'image.mimes' => 'Gambar harus bertipe jpeg, png, jpg, atau gif.',
                'image.max' => 'Ukuran gambar maksimal 2MB.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        $data = [
            'role' => $request->role,
            'name' => $request->name,
            'wa' => $request->wa,
            'status' => 'active',
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('staff_images', 'public');
            $data['image'] = $imagePath;
        }

        $staff = Staff::create($data);

        if ($staff->image) {
            $staff->image = url(Storage::url($staff->image));
        }

        return response()->json(['message' => 'Staff berhasil dibuat', 'data' => $staff], 201);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (!$staff || $staff->status === 'inactive') {
            return response()->json(['message' => 'Staff tidak ditemukan atau tidak aktif'], 404);
        }

        try {
            $request->validate([
                'role' => 'sometimes|string',
                'name' => 'sometimes|string|unique:staff,name,' . $staff->id,
                'wa' => 'sometimes|digits_between:8,15|numeric|unique:staff,wa,' . $staff->id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'role.string' => 'Role harus berupa teks.',
                'name.string' => 'Nama harus berupa teks.',
                'name.unique' => 'Nama sudah digunakan.',
                'wa.numeric' => 'Nomor WhatsApp hanya boleh berisi angka.',
                'wa.digits_between' => 'Nomor WhatsApp harus terdiri dari 8 hingga 15 digit.',
                'wa.unique' => 'Nomor WhatsApp sudah digunakan.',
                'image.image' => 'File harus berupa gambar.',
                'image.mimes' => 'Gambar harus bertipe jpeg, png, jpg, atau gif.',
                'image.max' => 'Ukuran gambar maksimal 2MB.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        $data = $request->only('role', 'name', 'wa');

        if ($request->hasFile('image')) {
            if ($staff->image) {
                Storage::disk('public')->delete($staff->image);
            }
            $imagePath = $request->file('image')->store('staff_images', 'public');
            $data['image'] = $imagePath;
        }

        $staff->update($data);

        if ($staff->image) {
            $staff->image = url(Storage::url($staff->image));
        }

        return response()->json(['message' => 'Staff berhasil diperbarui', 'data' => $staff]);
    }

    public function destroy($id)
    {
        $staff = Staff::find($id);
        if (!$staff || $staff->status === 'inactive') {
            return response()->json(['message' => 'Staff tidak ditemukan atau sudah nonaktif'], 404);
        }

        $staff->update(['status' => 'inactive']);
        return response()->json(['message' => 'Staff berhasil dinonaktifkan (soft delete)']);
    }
}
