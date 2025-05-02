<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class PromoteStudents extends Command
{
    protected $signature = 'students:promote';
    protected $description = 'Naikkan kelas siswa setiap 1 Juli';

    public function handle(): void
    {
        User::where('role', 'user')
            ->whereIn('class', ['10', '11', '12']) // hanya siswa aktif
            ->each(function ($user) {
                if ($user->class === '10') {
                    $user->class = '11';
                } elseif ($user->class === '11') {
                    $user->class = '12';
                } elseif ($user->class === '12') {
                    $user->class = 'lulus'; // langsung ubah ke 'lulus'
                }

                $user->save();
            });

        $this->info('Proses kenaikan kelas selesai.');
    }
}
