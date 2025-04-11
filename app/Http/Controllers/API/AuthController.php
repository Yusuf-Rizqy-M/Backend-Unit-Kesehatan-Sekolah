<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Pastikan hanya admin yang bisa mendaftarkan user baru (opsional)
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can register users.'
            ], 403);
        }

        // Validasi input
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:admin,user'
        ];

        $customMessages = [
            'email.email' => 'Jangan pakai email asal-asalan',
            'role.in' => 'Role harus admin atau user'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'All fields are required',
                'data' => $validator->errors()
            ], 422);
        }

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        // Buat token untuk autentikasi
        $success['token'] = $user->createToken('auth_token')->plainTextToken;
        $success['name'] = $user->name;
        $success['role'] = $user->role;

        return response()->json([
            'status' => true,
            'message' => 'Account created successfully',
            'data' => $success
        ], 201);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'remember' => 'nullable|boolean' // opsional, true/false
        ];

        $messages = [
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password tidak boleh kosong',
            'password.min' => 'Password minimal 8 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Waktu expired token
            $expiresAt = $request->remember
                ? Carbon::now()->addDays(30)
                : Carbon::now()->addHours(2);

            // Buat token dengan expired
            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'expired_at' => $expiresAt->toDateTimeString()
                ]
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Email atau password salah',
            'data' => null
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = User::where('id', $request->user()->id)->first();
        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ], 404);
        }
    }
}
