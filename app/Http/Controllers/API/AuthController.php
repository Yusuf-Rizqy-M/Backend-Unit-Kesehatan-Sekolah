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
        // Hanya admin yang bisa daftarin user
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can register users.'
            ], 403);
        }

        $role = $request->input('role');

        // Validasi dinamis tergantung role
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:admin,user',
        ];

        if ($role === 'user') {
            $rules['class'] = 'required|in:10,11,12';
            $rules['name_department'] = 'required|in:RPL,Animasi 3D,Animasi 2D,DKV DG,DKV TG';
            $rules['name_grades'] = 'required|string';
        }

        $customMessages = [
            'email.email' => 'Jangan pakai email asal-asalan',
            'role.in' => 'Role harus admin atau user',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        // Validasi kombinasi jurusan dan kelas (hanya jika role user)
        if ($role === 'user') {
            $validator->after(function ($validator) use ($request) {
                $department = $request->input('name_department');
                $grade = $request->input('name_grades');

                $rules = [
                    'RPL' => ['RPL 1', 'RPL 2'],
                    'Animasi 3D' => ['Animasi 3D 1', 'Animasi 3D 2', 'Animasi 3D 3'],
                    'Animasi 2D' => ['Animasi 2D 4', 'Animasi 2D 5'],
                    'DKV DG' => ['DKV DG 1', 'DKV DG 2', 'DKV DG 3'],
                    'DKV TG' => ['DKV TG 4', 'DKV TG 5'],
                ];

                if (isset($rules[$department]) && !in_array($grade, $rules[$department])) {
                    $validator->errors()->add('name_grades', 'Kelas dan jurusan tidak cocok dengan name_grades yang dipilih.');
                }
            });
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        // Simpan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'class' => $request->role === 'user' ? $request->class : null,
            'name_department' => $request->role === 'user' ? $request->name_department : null,
            'name_grades' => $request->role === 'user' ? $request->name_grades : null,
        ]);

        // Buat token
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

        // Check if user exists and is active before attempting authentication
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || $user->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'Akun tidak aktif atau tidak ditemukan',
                'data' => null
            ], 403);
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
                    'status' => $user->status, // Tambahkan status di sini
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
    public function updateClassAndDepartment(Request $request, $id)
    {
        // Hanya admin yang boleh akses
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can update class and department.'
            ], 403);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:3|max:255',
            'name_department' => 'nullable|in:RPL,Animasi 2D,Animasi 3D,DKV DG,DKV TG',
            'class' => 'nullable|in:10,11,12,Lulus,Keluar',
            'name_grades' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $department = $request->input('name_department');
        $grade = $request->input('name_grades');
        $class = $request->input('class');

        // Validasi kombinasi jurusan dan kelas hanya jika keduanya tersedia
        $rules = [
            'RPL' => ['RPL 1', 'RPL 2'],
            'Animasi 3D' => ['Animasi 3D 1', 'Animasi 3D 2', 'Animasi 3D 3'],
            'Animasi 2D' => ['Animasi 2D 4', 'Animasi 2D 5'],
            'DKV DG' => ['DKV DG 1', 'DKV DG 2', 'DKV DG 3'],
            'DKV TG' => ['DKV TG 4', 'DKV TG 5'],
        ];

        if ($department && $grade && !in_array($class, ['Lulus', 'Keluar'])) {
            if (!in_array($grade, $rules[$department] ?? [])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kelas dan jurusan tidak cocok dengan name_grades yang dipilih.'
                ], 422);
            }
        }

        // Update hanya field yang dikirim
        if ($request->filled('name'))
            $user->name = $request->name;
        if ($class)
            $user->class = $class;
        if ($grade)
            $user->name_grades = $grade;
        if ($department)
            $user->name_department = $department;

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User class, department, and name updated successfully',
            'data' => $user
        ], 200);
    }


}
