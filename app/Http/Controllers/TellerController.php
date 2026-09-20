<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TellerController extends Controller
{
    public function index(Request $request)
    {
        $katakunci = $request->cari;

        $query = DB::table('nasabah');
        if ($katakunci) {
            $query->where('nis', 'LIKE', "%" . $katakunci . "%")
                  ->orWhere('nama_siswa', 'LIKE', "%" . $katakunci . "%");
        }

        $nasabah = $query->get();

        // ALGORITMA SORTING PINTAR (Tingkat -> Jurusan -> SubKelas -> NIS)
        $nasabah = $nasabah->sortBy(function ($item) {
            $k = strtoupper($item->kelas);

            // 1. Deteksi Tingkat (Grade)
            $grade = 99;
            if (preg_match('/\bXII\b/', $k)) $grade = 3;
            elseif (preg_match('/\bXI\b/', $k)) $grade = 2;
            elseif (preg_match('/\bX\b/', $k)) $grade = 1;

            // 2. Deteksi Jurusan (Sesuai Urutan Kakak)
            $jurusan = 99;
            if (preg_match('/\bRPL\b/', $k)) $jurusan = 1;
            elseif (preg_match('/\bTKJ\b/', $k)) $jurusan = 2;
            elseif (preg_match('/\bDKV\b/', $k)) $jurusan = 3;
            elseif (preg_match('/\bAK\b/', $k)) $jurusan = 4;
            elseif (preg_match('/\bLPS\b/', $k)) $jurusan = 5;
            elseif (preg_match('/\bMP\b/', $k)) $jurusan = 6;
            elseif (preg_match('/\bBR\b/', $k)) $jurusan = 7;
            elseif (preg_match('/\bBD\b/', $k)) $jurusan = 8;

            // 3. Deteksi Sub-kelas (1/2/3/4 atau I/II/III/IV)
            $sub = 99;
            if (preg_match('/\b(?:4|IV)\b/', $k)) $sub = 4;
            elseif (preg_match('/\b(?:3|III)\b/', $k)) $sub = 3;
            elseif (preg_match('/\b(?:2|II)\b/', $k)) $sub = 2;
            elseif (preg_match('/\b(?:1|I)\b/', $k)) $sub = 1;

            // Menggabungkan skor dan NIS agar urut sempurna!
            return sprintf('%02d-%02d-%02d-%s', $grade, $jurusan, $sub, $item->nis);
        })->values();

        return view('teller.dashboard', compact('nasabah'));
    }

    public function cariByNis($nis)
    {
        $nasabah = DB::table('nasabah')->where('nis', $nis)->first();
        if ($nasabah) {
            return redirect('/teller/transaksi/'.$nasabah->id);
        }
        return redirect('/teller/dashboard')->with('error', 'Nasabah dengan NIS '.$nis.' tidak ditemukan!');
    }

    public function formTransaksi($id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        if (!$nasabah) {
            return redirect('/teller/dashboard')->with('error', 'Data nasabah tidak ditemukan!');
        }
        return view('teller.transaksi', compact('nasabah'));
    }

    public function prosesTransaksi(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required',
            'jenis_transaksi' => 'required|in:setor,tarik,admin',
            'nominal' => 'required|numeric|min:1000',
        ]);

        $nasabah_id = $request->nasabah_id;
        $jenis = $request->jenis_transaksi;
        $nominal = $request->nominal;
        $petugas_id = Auth::id();

        DB::beginTransaction();
        try {
            $nasabah = DB::table('nasabah')->where('id', $nasabah_id)->lockForUpdate()->first();

            // Aturan Saldo Mengendap Rp 10.000
            if ($jenis == 'tarik' && ($nasabah->saldo - $nominal) < 10000) {
                return back()->with('error', 'Gagal! Penarikan ditolak karena melanggar aturan saldo mengendap minimal Rp 10.000.');
            }

            // Aturan Potongan Admin
            if ($jenis == 'admin' && $nasabah->saldo < $nominal) {
                return back()->with('error', 'Gagal! Saldo nasabah tidak cukup untuk dipotong biaya administrasi.');
            }

            $transaksi_id = DB::table('transaksi')->insertGetId([
                'nasabah_id' => $nasabah_id, 'petugas_id' => $petugas_id,
                'jenis_transaksi' => $jenis, 'nominal' => $nominal,
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);

            $saldo_baru = ($jenis == 'setor') ? $nasabah->saldo + $nominal : $nasabah->saldo - $nominal;
            DB::table('nasabah')->where('id', $nasabah_id)->update(['saldo' => $saldo_baru]);

            $ket = match($jenis) {
                'setor' => 'Setoran Tunai dari ' . $nasabah->nama_siswa,
                'tarik' => 'Penarikan Tunai oleh ' . $nasabah->nama_siswa,
                'admin' => 'Biaya Administrasi buku dari ' . $nasabah->nama_siswa,
            };

            DB::table('jurnal')->insert([
                'transaksi_id' => $transaksi_id, 'keterangan' => $ket,
                'debit' => ($jenis == 'setor') ? $nominal : 0, 'kredit' => ($jenis != 'setor') ? $nominal : 0,
                'tanggal' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now(),
            ]);

            $brankasHariIni = DB::table('brankas')->where('tanggal', now()->toDateString())->first();

            if ($brankasHariIni) {
                $masuk = ($jenis == 'setor') ? $brankasHariIni->masuk + $nominal : $brankasHariIni->masuk;
                $keluar = ($jenis == 'tarik') ? $brankasHariIni->keluar + $nominal : $brankasHariIni->keluar;
                $total = $brankasHariIni->saldo_awal + $masuk - $keluar;

                DB::table('brankas')->where('id', $brankasHariIni->id)->update([
                    'masuk' => $masuk, 'keluar' => $keluar, 'total' => $total, 'updated_at' => now(),
                ]);
            } else {
                $kemarin = DB::table('brankas')->orderBy('tanggal', 'desc')->first();
                $saldo_awal = $kemarin ? $kemarin->total : 0;
                $masuk = ($jenis == 'setor') ? $nominal : 0;
                $keluar = ($jenis == 'tarik') ? $nominal : 0;
                $total = $saldo_awal + $masuk - $keluar;

                DB::table('brankas')->insert([
                    'tanggal' => now()->toDateString(), 'saldo_awal' => $saldo_awal,
                    'masuk' => $masuk, 'keluar' => $keluar, 'total' => $total,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect('/teller/dashboard')->with('success', 'Transaksi berhasil diproses! Menunggu persetujuan Supervisor.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Sistem Gagal: ' . $e->getMessage());
        }
    }

    public function riwayat()
    {
        $riwayat = DB::table('transaksi')
                    ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                    ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis')
                    ->where('transaksi.petugas_id', Auth::id())
                    ->orderBy('transaksi.created_at', 'desc')
                    ->get();

        return view('teller.riwayat', compact('riwayat'));
    }

    public function scanQr()
    {
        return view('teller.scan');
    }

    public function profil()
    {
        $user = Auth::user();
        return view('teller.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $dataUpdate = ['no_hp' => $request->no_hp, 'updated_at' => now()];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public', $filename);
            $dataUpdate['foto'] = $filename;
        }

        DB::table('users')->where('id', $user->id)->update($dataUpdate);
        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    // FUNGSI VOID / PEMBATALAN TRANSAKSI
    public function voidTransaksi(Request $request, $id)
    {
        $request->validate([
            'pin_supervisor' => 'required',
            'alasan_batal' => 'required|min:5'
        ]);

        // Cek PIN Otorisasi Supervisor
        if ($request->pin_supervisor !== '123456') {
            return back()->with('error', 'Gagal Void! PIN Otorisasi Supervisor SALAH.');
        }

        DB::beginTransaction();
        try {
            $trxLama = DB::table('transaksi')->where('id', $id)->lockForUpdate()->first();
            if (!$trxLama || $trxLama->status == 'dibatalkan') {
                return back()->with('error', 'Transaksi tidak valid atau sudah dibatalkan.');
            }

            $nasabah = DB::table('nasabah')->where('id', $trxLama->nasabah_id)->lockForUpdate()->first();

            // Reversal Saldo Nasabah
            if ($trxLama->jenis_transaksi == 'setor') {
                $saldo_baru = $nasabah->saldo - $trxLama->nominal;
            } else {
                $saldo_baru = $nasabah->saldo + $trxLama->nominal;
            }
            DB::table('nasabah')->where('id', $nasabah->id)->update(['saldo' => $saldo_baru]);

            // Update status transaksi
            DB::table('transaksi')->where('id', $id)->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $request->alasan_batal,
                'updated_at' => now()
            ]);

            // Buat Jurnal Balik
            $ket = 'KOREKSI VOID: Pembatalan transaksi ' . $trxLama->jenis_transaksi . ' (' . $request->alasan_batal . ')';
            DB::table('jurnal')->insert([
                'transaksi_id' => $trxLama->id, 'keterangan' => $ket,
                'debit' => ($trxLama->jenis_transaksi != 'setor') ? $trxLama->nominal : 0,
                'kredit' => ($trxLama->jenis_transaksi == 'setor') ? $trxLama->nominal : 0,
                'tanggal' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now(),
            ]);

            // Reversal Brankas
            $brankas = DB::table('brankas')->where('tanggal', now()->toDateString())->first();
            if ($brankas) {
                $masuk = ($trxLama->jenis_transaksi == 'setor') ? $brankas->masuk - $trxLama->nominal : $brankas->masuk;
                $keluar = ($trxLama->jenis_transaksi == 'tarik') ? $brankas->keluar - $trxLama->nominal : $brankas->keluar;
                $total = $brankas->saldo_awal + $masuk - $keluar;
                DB::table('brankas')->where('id', $brankas->id)->update([
                    'masuk' => $masuk, 'keluar' => $keluar, 'total' => $total, 'updated_at' => now()
                ]);
            }

            DB::commit();
            return back()->with('success', 'Transaksi Berhasil Di-VOID! Saldo Nasabah telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal Void Sistem: ' . $e->getMessage());
        }
    }
}
