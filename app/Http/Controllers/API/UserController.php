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
     * Get profile user yang sedang login.
     */
    public function show()
    {
        $user = Auth::user(); 
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => $user
        ], 200);
    }

    /**
     * Update profile user yang sedang login.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
    
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:3|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|max:15',
            'gender' => 'sometimes|in:male,female',
            'name_grades' => 'sometimes|in:RPL,Animasi,DKV',
            'class' => 'sometimes|in:X,XI,XII',
            'no_hp_parent' => 'sometimes|string|max:15',
            'name_parent' => 'sometimes|string|max:255',
            'name_walikelas' => 'sometimes|string|max:255',
            'address_walikelas' => 'sometimes|string|max:255',
            'absent' => 'sometimes|integer|min:1|max:40',
            'current_password' => 'required_with:password|string|min:8',
            'password' => 'sometimes|string|min:8|max:255|confirmed',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Update data yang diisi oleh user
        $user->fill($request->except(['current_password', 'password']));
    
        // Jika ingin mengganti password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }
    
            $user->password = bcrypt($request->password);
        }
    
        $user->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }    
}
