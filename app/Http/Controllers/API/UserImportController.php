<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\User;
use App\Models\Department;
use App\Models\Grade;

class UserImportController extends Controller
{
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $rows = Excel::toCollection(new UsersImport, $request->file('file'))->first();

        $created = [];
        $failed = [];
        $allowedClasses = ['10', '11', '12'];

        foreach ($rows as $index => $row) {
            // Cek kolom wajib
            if (
                !isset(
                    $row['name'],
                    $row['email'],
                    $row['password'],
                    $row['department_id'],
                    $row['grade_id'],
                    $row['class'],
                    $row['role']
                )
            ) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Kolom wajib tidak lengkap'
                ];
                continue;
            }

            // Cek format email harus @gmail.com
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($row['email']), '@gmail.com')) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Email harus menggunakan Gmail yang valid (@gmail.com)'
                ];
                continue;
            }

            // Pastikan class sesuai enum (cast ke string biar aman dari Excel angka)
            $classValue = (string)$row['class'];
            if (!in_array($classValue, $allowedClasses)) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Class tidak valid. Harus salah satu dari: ' . implode(', ', $allowedClasses)
                ];
                continue;
            }

            // Validasi department
            $department = Department::find($row['department_id']);
            if (!$department) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Department ID tidak ditemukan'
                ];
                continue;
            }

            // Validasi grade
            $grade = Grade::find($row['grade_id']);
            if (!$grade) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Grade ID tidak ditemukan'
                ];
                continue;
            }

            // Pastikan grade sesuai dengan department
            if ($grade->department_id != $department->id) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Grade tidak sesuai dengan department'
                ];
                continue;
            }

            // Cek email unik
            if (User::where('email', $row['email'])->exists()) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Email sudah digunakan'
                ];
                continue;
            }

            // Simpan user
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => bcrypt($row['password']),
                'role' => $row['role'],
                'class' => $classValue, // class selalu string
                'department_id' => $department->id,
                'grade_id' => $grade->id,
                'name_department' => $department->name,
                'name_grades' => $grade->name,
            ]);

            $created[] = [
                'row' => $index + 2,
                'id' => $user->id,
                'email' => $user->email
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proses import selesai',
            'data' => [
                'created_count' => count($created),
                'failed_count' => count($failed),
                'created' => $created,
                'failed' => $failed
            ]
        ], 200);
    }
}