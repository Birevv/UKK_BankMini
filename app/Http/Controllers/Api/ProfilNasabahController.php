<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfilNasabahController extends Controller
{
    // 1. CEK SALDO & DATA PROFIL
    public function saldo(Request $request)
    {
        $noRekening = $request->query('no_rekening');
        $nasabah = DB::table('nasabah')->where('no_rekening', $noRekening)->first();

        if (!$nasabah) {
            return response()->json(['success' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa'  => $nasabah->nama_siswa,
                'saldo'        => (float) $nasabah->saldo,
            ]
        ]);
    }

    // 2. CEK REKENING TUJUAN (INQUIRY SEBELUM TRANSFER)
    public function cekRekening(Request $request)
    {
        $noRekening = $request->query('no_rekening') ?? $request->no_rekening;

        if (!$noRekening) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no_rekening diperlukan'
            ], 400);
        }

        $nasabah = DB::table('nasabah')
            ->where('no_rekening', $noRekening)
            ->first();

        if (!$nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor rekening tujuan tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekening ditemukan',
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa'  => $nasabah->nama_siswa,
                'kelas'       => $nasabah->kelas,
                'jurusan'     => $nasabah->jurusan,
            ]
        ]);
    }
}
