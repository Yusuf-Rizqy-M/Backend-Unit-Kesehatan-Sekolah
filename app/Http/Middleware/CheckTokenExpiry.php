<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckTokenExpiry
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Cek apakah token yang digunakan valid dan belum expired
        if ($user && $user->currentAccessToken()) {
            $expiresAt = $user->currentAccessToken()->expires_at;

            if ($expiresAt && Carbon::now()->greaterThan($expiresAt)) {
                // Token sudah kedaluwarsa
                $user->currentAccessToken()->delete(); // hapus token
                return response()->json([
                    'success' => false,
                    'message' => 'Token sudah kedaluwarsa, silakan login ulang.',
                ], 401);
            }
        }

        return $next($request);
    }
}

