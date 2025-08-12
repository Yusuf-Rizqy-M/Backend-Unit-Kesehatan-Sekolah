<?php

namespace App\Http\Controllers\API;
use App\Models\Department;
use App\Models\Grade;
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

        // Validasi awal
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:admin,user',
        ];

        if ($role === 'user') {
            $rules['department_id'] = 'required|exists:departments,id';
            $rules['grade_id'] = 'required|exists:grades,id';
            $rules['class'] = 'required|in:10,11,12';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        // Tambahan pengecekan department & grade cocok
        if ($role === 'user') {
            $grade = Grade::find($request->grade_id);
            if (!$grade || $grade->department_id != $request->department_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'The selected grade does not belong to the selected department.'
                ], 422);
            }
        }

        // Ambil name_department dan name_grades dari tabel lain
        $departmentName = null;
        $gradeName = null;

        if ($role === 'user') {
            $departmentName = Department::where('id', $request->department_id)->value('name');
            $gradeName = Grade::where('id', $request->grade_id)->value('name');
        }

        // Simpan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'class' => $role === 'user' ? $request->class : null,
            'name_department' => $departmentName,
            'name_grades' => $gradeName,
        ]);

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
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can update class and department.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:3|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'grade_id' => 'nullable|exists:grades,id',
            'class' => 'nullable|in:10,11,12,Lulus,Keluar',
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

        // Ambil name_department & name_grades dari tabel lain
        if ($request->filled('department_id')) {
            $user->name_department = Department::where('id', $request->department_id)->value('name');
        }

        if ($request->filled('grade_id')) {
            $user->name_grades = Grade::where('id', $request->grade_id)->value('name');
        }

        if ($request->filled('class')) {
            $user->class = $request->class;
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

}
