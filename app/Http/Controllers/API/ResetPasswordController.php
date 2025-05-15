<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class ResetPasswordController extends Controller
{
    public function requestReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 404);
        }
    
        $otp = random_int(1000, 9999); // OTP 4 digit
    
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );
    
        try {
            // Kirim OTP via email menggunakan OtpMail
            Mail::to($request->email)->send(new OtpMail($otp));
        } catch (Exception $e) {
            // Log detailed error if email sending fails
            \Log::error('Failed to send OTP email to ' . $request->email . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim email OTP. Silakan coba lagi.'], 500);
        }
    
        return response()->json(['message' => 'Token OTP telah dikirim ke email Anda.'], 200);
    }
    
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|digits:4',
            'password' => 'required|confirmed|min:8',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Token tidak valid.'], 422);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'Token telah kedaluwarsa.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|digits:4',
        ]);
    
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();
    
        if (!$reset) {
            return response()->json(['message' => 'Token tidak valid.'], 422);
        }
    
        if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'Token telah kedaluwarsa.'], 422);
        }
    
        return response()->json(['message' => 'Token valid.']);
    }
}