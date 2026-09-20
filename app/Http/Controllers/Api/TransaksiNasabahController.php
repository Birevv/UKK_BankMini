<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransaksiNasabahController extends Controller
{
    // 1. TRANSFER SALDO ANTAR NASABAH (E-WALLET / MOBILE)
    public function transfer(Request $request)
    {
        $request->validate([
            'no_rekening_pengirim' => 'required',
            'no_rekening_tujuan'   => 'required',
            'nominal'              => 'required|numeric|min:1000',
            'pin'                  => 'required|digits:6',
            'catatan'              => 'nullable|string|max:100',
        ]);

        if ($request->no_rekening_pengirim === $request->no_rekening_tujuan) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan transfer ke rekening sendiri!'
            ], 400);
        }

        $pengirim = DB::table('nasabah')->where('no_rekening', $request->no_rekening_pengirim)->first();
        if (!$pengirim) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening pengirim tidak valid!'
            ], 404);
        }

        // Verifikasi PIN Pengirim
        if (!Hash::check($request->pin, $pengirim->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN Keamanan salah!'
            ], 400);
        }

        $penerima = DB::table('nasabah')->where('no_rekening', $request->no_rekening_tujuan)->first();
        if (!$penerima) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tujuan tidak ditemukan!'
            ], 404);
        }

        $nominal = (int) $request->nominal;

        if ($pengirim->saldo < $nominal) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Anda tidak mencukupi untuk transfer ini!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Kurangi saldo pengirim
            DB::table('nasabah')->where('id', $pengirim->id)->decrement('saldo', $nominal);

            // 2. Tambah saldo penerima
            DB::table('nasabah')->where('id', $penerima->id)->increment('saldo', $nominal);

            $catatan = $request->catatan ? ' (' . $request->catatan . ')' : '';

            // 3. Catat mutasi keluar pada akun pengirim
            DB::table('transaksi')->insert([
                'nasabah_id'      => $pengirim->id,
                'jenis_transaksi' => 'transfer_keluar',
                'nominal'         => $nominal,
                'nama_petugas'    => 'Transfer ke ' . $penerima->nama_siswa . $catatan,
                'status'          => 'sukses',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // 4. Catat mutasi masuk pada akun penerima
            DB::table('transaksi')->insert([
                'nasabah_id'      => $penerima->id,
                'jenis_transaksi' => 'transfer_masuk',
                'nominal'         => $nominal,
                'nama_petugas'    => 'Transfer dari ' . $pengirim->nama_siswa . $catatan,
                'status'          => 'sukses',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            $sisaSaldo = $pengirim->saldo - $nominal;

            return response()->json([
                'success' => true,
                'message' => 'Transfer sejumlah Rp ' . number_format($nominal, 0, ',', '.') . ' ke ' . $penerima->nama_siswa . ' berhasil!',
                'data'    => [
                    'nominal'              => $nominal,
                    'no_rekening_tujuan'   => $penerima->no_rekening,
                    'nama_penerima'        => $penerima->nama_siswa,
                    'sisa_saldo'           => (float) $sisaSaldo,
                    'waktu'                => now()->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. RIWAYAT TRANSAKSI / MUTASI
    public function riwayat(Request $request)
    {
        $noRekening = $request->query('no_rekening');
        $idNasabah = $request->query('id_nasabah');

        $riwayat = DB::table('transaksi')
            ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
            ->when($noRekening, function ($query, $noRekening) {
                return $query->where('nasabah.no_rekening', $noRekening);
            })
            ->when($idNasabah, function ($query, $idNasabah) {
                return $query->where('transaksi.nasabah_id', $idNasabah);
            })
            ->select('transaksi.*', 'nasabah.no_rekening', 'nasabah.nama_siswa')
            ->orderBy('transaksi.id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $riwayat
        ]);
    }

    // 3. RINGKASAN PEMASUKAN & PENGELUARAN BULAN INI
    public function ringkasan(Request $request)
    {
        $noRekening = $request->query('no_rekening');
        $nasabah = DB::table('nasabah')->where('no_rekening', $noRekening)->first();

        if (!$nasabah) {
            return response()->json(['success' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        // Pemasukan bulan ini: setor + transfer_masuk
        $totalMasuk = DB::table('transaksi')
            ->where('nasabah_id', $nasabah->id)
            ->whereIn('jenis_transaksi', ['setor', 'transfer_masuk'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        // Pengeluaran bulan ini: tarik + transfer_keluar
        $totalKeluar = DB::table('transaksi')
            ->where('nasabah_id', $nasabah->id)
            ->whereIn('jenis_transaksi', ['tarik', 'transfer_keluar'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        return response()->json([
            'success' => true,
            'data'    => [
                'no_rekening'  => $nasabah->no_rekening,
                'nama_siswa'   => $nasabah->nama_siswa,
                'saldo'        => (float) $nasabah->saldo,
                'total_masuk'  => (float) $totalMasuk,
                'total_keluar' => (float) $totalKeluar,
            ]
        ]);
    }
}
