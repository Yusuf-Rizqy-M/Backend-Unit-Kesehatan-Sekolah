<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check_token_expiry' => \App\Http\Middleware\CheckTokenExpiry::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Menjalankan command expire antrian setiap hari
        $schedule->command('queue:expire')->dailyAt('00:00');

        // Menjalankan kenaikan kelas siswa setiap tanggal 1 Juli jam 00:01
        $schedule->command('students:promote')
                 ->yearlyOn(7, 1, '00:01');
    })
    ->create();
