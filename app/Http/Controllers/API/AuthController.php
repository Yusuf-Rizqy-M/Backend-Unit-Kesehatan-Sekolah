<?php

namespace App\Http\Controllers\API;

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
                'success' => false,
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
                'success' => false,
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
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $success
        ], 201);
    }
    

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ];

        $customMessages = [
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Jangan pakai email asal-asalan',
            'password.required' => 'Password tidak boleh kosong',
            'password.min' => 'Password minimal 8 karakter'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'All fields are required',
                'data' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();
            $success['token'] = $auth->createToken('auth_token')->plainTextToken;
            $success['name'] = $auth->name;
            $success['email'] = $auth->email;
            $success['role'] = $auth->role;

            return response()->json([
                'success' => true,
                'message' => 'Login success',
                'data' => $success
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
                'data' => null
            ], 401);
        }
    }
}
