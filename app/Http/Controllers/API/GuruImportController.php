<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GurusImport;
use App\Models\Guru;

class GuruImportController extends Controller
{
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $rows = Excel::toCollection(new GurusImport, $request->file('file'))->first();

        $created = [];
        $failed = [];
        $allowedJenisKelamin = ['Laki-laki', 'Perempuan'];

        foreach ($rows as $index => $row) {
            // Kolom wajib
            if (
                !isset(
                    $row['nama'],
                    $row['no_hp'],
                    $row['email'],
                    $row['jenis_kelamin'],
                    $row['mata_pelajaran'],
                    $row['status']
                )
            ) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Kolom wajib tidak lengkap'
                ];
                continue;
            }

            // Validasi email unik & format
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Format email tidak valid'
                ];
                continue;
            }

            if (Guru::where('email', $row['email'])->exists()) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Email sudah digunakan'
                ];
                continue;
            }

            // Validasi jenis kelamin
            if (!in_array($row['jenis_kelamin'], $allowedJenisKelamin)) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Jenis kelamin harus Laki-laki atau Perempuan'
                ];
                continue;
            }

            // Validasi status
            if (!in_array(strtolower($row['status']), ['active', 'inactive'])) {
                $failed[] = [
                    'row' => $index + 2,
                    'reason' => 'Status tidak valid. Harus aktif atau non-aktif'
                ];
                continue;
            }

            // Simpan guru
            $guru = Guru::create([
                'nama' => $row['nama'],
                'no_hp' => $row['no_hp'],
                'email' => $row['email'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'mata_pelajaran' => $row['mata_pelajaran'],
                'alamat' => $row['alamat'] ?? null,
                'status' => strtolower($row['status']),
            ]);

            $created[] = [
                'row' => $index + 2,
                'id' => $guru->id,
                'email' => $guru->email
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proses import guru selesai',
            'data' => [
                'created_count' => count($created),
                'failed_count' => count($failed),
                'created' => $created,
                'failed' => $failed
            ]
        ], 200);
    }
}
