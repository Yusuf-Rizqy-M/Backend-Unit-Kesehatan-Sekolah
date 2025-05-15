<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Storage;
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

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|numeric|digits_between:10,15',
            'gender' => 'sometimes|in:male,female',
            'no_hp_parent' => 'sometimes|numeric|digits_between:10,15',
            'name_parent' => 'sometimes|string|max:255',
            'name_walikelas' => 'sometimes|string|max:255',
            'absent' => 'sometimes|integer|min:1|max:40',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ambil data yang boleh diubah
        $data = $request->except(['name', 'class', 'name_grades', 'name_department']);
        $user->fill($data);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }


    public function updatePlayerId(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string',
        ]);

        $user = Auth::user();
        $user->player_id = $request->player_id;
        $user->save();

        return response()->json(['message' => 'Player ID updated']);
    }
    /**
     * Mengubah status user menjadi inactive (soft delete).
     * Hanya dapat diakses oleh admin.
     * Contoh: DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->status = 'inactive';
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User deactivated (soft deleted) successfully',
            'data' => $user
        ]);
    }

}
