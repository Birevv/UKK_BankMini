<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Nasabah;

class AuthNasabahController extends Controller
{
    // 1. LOGIN NASABAH VIA MOBILE
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $nasabah = Nasabah::where('username', $request->username)->first();

        if (!$nasabah || !Hash::check($request->password, $nasabah->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah!'
            ], 401);
        }

        // Generate Sanctum Bearer Token
        $token = $nasabah->createToken('nasabah-token')->plainTextToken;

        return response()->json([
            'success'    => true,
            'message'    => 'Login Berhasil',
            'token'      => $token,
            'token_type' => 'Bearer',
            'data'       => [
                'id_nasabah'  => $nasabah->id,
                'username'    => $nasabah->username,
                'nama_siswa'  => $nasabah->nama_siswa,
                'no_rekening' => $nasabah->no_rekening,
            ]
        ], 200);
    }

    // 2. LOGOUT NASABAH VIA MOBILE
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil'
        ], 200);
    }

    // 3. VERIFIKASI PIN 6-DIGIT OTORISASI
    public function verifyPin(Request $request)
    {
        $request->validate([
            'no_rekening' => 'required',
            'pin'         => 'required|digits:6'
        ]);

        $nasabah = DB::table('nasabah')->where('no_rekening', $request->no_rekening)->first();

        if (!$nasabah || !Hash::check($request->pin, $nasabah->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN 6-digit keamanan salah!'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Otorisasi PIN Berhasil!'
        ]);
    }

    // 4. GANTI PASSWORD LOGIN
    public function gantiPassword(Request $request)
    {
        $request->validate([
            'no_rekening'   => 'required',
            'password_lama' => 'required',
            'password_baru' => 'required|min:6',
        ]);

        $nasabah = DB::table('nasabah')->where('no_rekening', $request->no_rekening)->first();

        if (!$nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak ditemukan!'
            ], 404);
        }

        if (!Hash::check($request->password_lama, $nasabah->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai!'
            ], 400);
        }

        DB::table('nasabah')->where('id', $nasabah->id)->update([
            'password'   => Hash::make($request->password_baru),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password login berhasil diperbarui!'
        ]);
    }

    // 5. GANTI PIN TRANSAKSI (6-DIGIT)
    public function gantiPin(Request $request)
    {
        $request->validate([
            'no_rekening' => 'required',
            'pin_lama'    => 'required|digits:6',
            'pin_baru'    => 'required|digits:6',
        ]);

        $nasabah = DB::table('nasabah')->where('no_rekening', $request->no_rekening)->first();

        if (!$nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak ditemukan!'
            ], 404);
        }

        if (!Hash::check($request->pin_lama, $nasabah->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN lama salah!'
            ], 400);
        }

        DB::table('nasabah')->where('id', $nasabah->id)->update([
            'pin_keamanan' => Hash::make($request->pin_baru),
            'updated_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN transaksi 6-digit berhasil diperbarui!'
        ]);
    }
}
