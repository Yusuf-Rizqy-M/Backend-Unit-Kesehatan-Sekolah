<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Menampilkan semua user dari database.
     * Hanya dapat diakses oleh admin.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => true,
            'message' => 'All users retrieved successfully',
            'data' => $users
        ], 200);
    }

    /**
     * Mencari user berdasarkan nama.
     * Gunakan query param `?name=nama`.
     * Contoh: /api/users/search?name=agus
     * Hanya dapat diakses oleh admin.
     */
    public function search(Request $request)
    {
        $name = $request->query('name');

        $request->validate([
            'name' => 'required|string'
        ]);

        $users = User::where('name', 'like', '%' . $name . '%')->get();

        return response()->json([
            'status' => true,
            'message' => 'Search results',
            'data' => $users
        ]);
    }

    /**
     * Menampilkan detail user berdasarkan ID.
     * Hanya dapat diakses oleh admin.
     * Contoh: /api/users/5
     */
    public function showById($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * Menampilkan profil user yang sedang login.
     * Bisa diakses oleh user dan admin.
     * Contoh: /api/profile
     */
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'message' => 'User profile retrieved successfully',
            'data' => $user
        ], 200);
    }

    /**
     * Mengupdate profil user yang sedang login.
     * Hanya bisa mengubah data tertentu, tidak termasuk password.
     * Bisa diakses oleh user dan admin.
     * Contoh: PUT /api/profile/update
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi inputan user
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:3|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|numeric|digits_between:10,15',
            'gender' => 'sometimes|in:male,female',
            'name_grades' => 'sometimes|in:RPL,Animasi,DKV',
            'class' => 'sometimes|in:X,XI,XII',
            'no_hp_parent' => 'sometimes|numeric|digits_between:10,15',
            'name_parent' => 'sometimes|string|max:255',
            'name_walikelas' => 'sometimes|string|max:255',
            'absent' => 'sometimes|integer|min:1|max:40',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update data profil
        $user->fill($request->all());
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }
}
