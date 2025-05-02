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

class ResetPasswordController extends Controller
{
    public function requestReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        $user = User::where('email', $request->email)->first();
    
        // untuk keamanan, jangan beri tahu kalau email tidak terdaftar
        if (!$user) return response()->json(['message' => 'Jika email terdaftar, token telah dikirim.']);
    
        $otp = random_int(1000, 9999); // OTP 4 digit
    
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now()
            ]
        );
    
        // Kirim OTP via email menggunakan OtpMail
        Mail::to($request->email)->send(new OtpMail($otp));
    
        return response()->json(['message' => 'Jika email terdaftar, token telah dikirim.']);
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
