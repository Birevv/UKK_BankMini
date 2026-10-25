<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TerbilangHelper;

class BukuTabunganController extends Controller
{
    /**
     * Tampilkan Halaman Buku Tabungan Fisik / Buku Besar Pembantu Nasabah
     */
    public function show($id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        if (!$nasabah) {
            return back()->with('error', 'Data nasabah tidak ditemukan!');
        }

        // Ambil seluruh riwayat mutasi transaksi nasabah
        $transaksi = DB::table('transaksi')
            ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
            ->select('transaksi.*', 'users.nama_petugas as nama_teller')
            ->where('transaksi.nasabah_id', $id)
            ->where('transaksi.status', '!=', 'dibatalkan')
            ->orderBy('transaksi.created_at', 'asc')
            ->get();

        // Hitung Saldo Berjalan (Running Balance) untuk format buku tabungan
        $saldoBerjalan = 0;
        $totalSetor = 0;
        $totalTarik = 0;

        foreach ($transaksi as $trx) {
            if ($trx->jenis_transaksi === 'setor') {
                $saldoBerjalan += $trx->nominal;
                $totalSetor += $trx->nominal;
            } else {
                $saldoBerjalan -= $trx->nominal;
                $totalTarik += $trx->nominal;
            }
            $trx->saldo_berjalan = $saldoBerjalan;
        }

        $role = Auth::user()->role ?? 'teller';

        return view('nasabah.buku-tabungan', compact(
            'nasabah',
            'transaksi',
            'totalSetor',
            'totalTarik',
            'role'
        ));
    }

    /**
     * Cetak Lembar Buku Tabungan (Ukuran Buku Bank Mini Biru)
     */
    public function cetakBuku($id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        if (!$nasabah) {
            return back()->with('error', 'Data nasabah tidak ditemukan!');
        }

        $transaksi = DB::table('transaksi')
            ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
            ->select('transaksi.*', 'users.nama_petugas as nama_teller')
            ->where('transaksi.nasabah_id', $id)
            ->where('transaksi.status', '!=', 'dibatalkan')
            ->orderBy('transaksi.created_at', 'asc')
            ->get();

        $saldoBerjalan = 0;
        foreach ($transaksi as $trx) {
            if ($trx->jenis_transaksi === 'setor') {
                $saldoBerjalan += $trx->nominal;
            } else {
                $saldoBerjalan -= $trx->nominal;
            }
            $trx->saldo_berjalan = $saldoBerjalan;
        }

        return view('nasabah.cetak-buku', compact('nasabah', 'transaksi'));
    }

    /**
     * Cetak Slip Setoran Tunai (2 Lembar Rangkap: Arsip Bank Mini & Nasabah)
     */
    public function cetakSlipSetor($id)
    {
        $trx = DB::table('transaksi')
            ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
            ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
            ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.no_rekening', 'nasabah.nis', 'nasabah.kelas', 'nasabah.jurusan', 'users.nama_petugas as nama_teller')
            ->where('transaksi.id', $id)
            ->first();

        if (!$trx) {
            return back()->with('error', 'Data transaksi tidak ditemukan!');
        }

        $terbilang = TerbilangHelper::terbilang($trx->nominal);

        return view('teller.slip-setoran', compact('trx', 'terbilang'));
    }

    /**
     * Cetak Slip Penarikan Tunai
     */
    public function cetakSlipTarik($id)
    {
        $trx = DB::table('transaksi')
            ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
            ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
            ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.no_rekening', 'nasabah.nis', 'nasabah.kelas', 'nasabah.jurusan', 'users.nama_petugas as nama_teller')
            ->where('transaksi.id', $id)
            ->first();

        if (!$trx) {
            return back()->with('error', 'Data transaksi tidak ditemukan!');
        }

        $terbilang = TerbilangHelper::terbilang($trx->nominal);

        return view('teller.slip-penarikan', compact('trx', 'terbilang'));
    }

    /**
     * Ubah Status Nasabah (Aktif vs Alumni untuk cegah database bloat)
     */
    public function ubahStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,alumni,nonaktif'
        ]);

        DB::table('nasabah')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Status nasabah berhasil diperbarui!');
    }
}
