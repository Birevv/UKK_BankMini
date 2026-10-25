<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengajuanKasController extends Controller
{
    /**
     * Tampilan Pengajuan Kas untuk Teller (Formulir & Riwayat Sendiri)
     */
    public function indexTeller(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        // Cek kondisi kas fisik saat ini di brankas loket
        $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        // Riwayat pengajuan kas milik teller yang login
        $pengajuanList = DB::table('pengajuan_kas')
            ->leftJoin('users as spv', 'pengajuan_kas.disetujui_oleh', '=', 'spv.id')
            ->select('pengajuan_kas.*', 'spv.nama_petugas as nama_spv')
            ->where('pengajuan_kas.user_id', $userId)
            ->orderBy('pengajuan_kas.created_at', 'desc')
            ->get();

        return view('teller.pengajuan-kas', compact('kasTersedia', 'kasAwal', 'pengajuanList'));
    }

    /**
     * Teller Mengirim Pengajuan Kas Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'alasan' => 'required|string|max:500',
        ]);

        $today = Carbon::today()->toDateString();
        $countToday = DB::table('pengajuan_kas')->whereDate('tanggal', $today)->count() + 1;
        $nomorPengajuan = 'PK-' . date('Ymd') . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);

        DB::table('pengajuan_kas')->insert([
            'nomor_pengajuan' => $nomorPengajuan,
            'user_id' => Auth::id(),
            'tanggal' => $today,
            'nominal' => $request->nominal,
            'alasan' => $request->alasan,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Pengajuan kas #{$nomorPengajuan} sebesar Rp " . number_format($request->nominal, 0, ',', '.') . " berhasil dikirim! Menunggu persetujuan Supervisor / Accounting.");
    }

    /**
     * Tampilan Pengajuan Kas untuk Supervisor & Accounting
     */
    public function indexSupervisor(Request $request)
    {
        $today = Carbon::today()->toDateString();

        // Info Kas Saat Ini
        $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        $query = DB::table('pengajuan_kas')
            ->join('users as teller', 'pengajuan_kas.user_id', '=', 'teller.id')
            ->leftJoin('users as spv', 'pengajuan_kas.disetujui_oleh', '=', 'spv.id')
            ->select(
                'pengajuan_kas.*',
                'teller.nama_petugas as nama_teller',
                'spv.nama_petugas as nama_spv'
            );

        if ($request->filled('status')) {
            $query->where('pengajuan_kas.status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('pengajuan_kas.tanggal', $request->tanggal);
        }

        $pengajuanList = $query->orderBy('pengajuan_kas.created_at', 'desc')->get();

        $role = strtolower(Auth::user()->role ?? 'supervisor');

        return view('supervisor.pengajuan-kas', compact('kasTersedia', 'kasAwal', 'pengajuanList', 'role'));
    }

    /**
     * Supervisor / Accounting Menyetujui Pengajuan Kas (Kas Otomatis Bertambah)
     */
    public function setujui(Request $request, $id)
    {
        $pengajuan = DB::table('pengajuan_kas')->where('id', $id)->first();
        if (!$pengajuan) {
            return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
        }

        if ($pengajuan->status !== 'pending') {
            return back()->with('error', 'Pengajuan kas ini sudah pernah diproses sebelumnya.');
        }

        $today = Carbon::today()->toDateString();

        DB::beginTransaction();
        try {
            // 1. Update status pengajuan kas
            DB::table('pengajuan_kas')->where('id', $id)->update([
                'status' => 'disetujui',
                'disetujui_oleh' => Auth::id(),
                'catatan_spv' => $request->catatan_spv ?? 'Disetujui untuk pemenuhan likuiditas loket',
                'updated_at' => now(),
            ]);

            // 2. Tambahkan dana masuk ke kas brankas hari ini
            $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->lockForUpdate()->first();
            if ($brankasHariIni) {
                $masuk = $brankasHariIni->masuk + $pengajuan->nominal;
                $total = $brankasHariIni->saldo_awal + $masuk - $brankasHariIni->keluar;

                DB::table('brankas')->where('id', $brankasHariIni->id)->update([
                    'masuk' => $masuk,
                    'total' => $total,
                    'updated_at' => now(),
                ]);
            } else {
                $kemarin = DB::table('brankas')->orderBy('tanggal', 'desc')->first();
                $saldo_awal = $kemarin ? $kemarin->total : 0;
                $total = $saldo_awal + $pengajuan->nominal;

                DB::table('brankas')->insert([
                    'tanggal' => $today,
                    'saldo_awal' => $saldo_awal,
                    'masuk' => $pengajuan->nominal,
                    'keluar' => 0,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Catat di Jurnal Akuntansi sebagai Droping Dana Kas Operasional
            // Cari petugas teller terkait
            $teller = DB::table('users')->where('id', $pengajuan->user_id)->first();
            $namaTeller = $teller ? $teller->nama_petugas : 'Teller';

            // Catat di Jurnal Akuntansi sebagai Droping Dana Kas Operasional
            DB::table('jurnal')->insert([
                'transaksi_id' => null, // null = Transaksi Non-Nasabah / Droping Kas Internal
                'keterangan' => "PENERIMAAN DROPING KAS OPERASIONAL [{$pengajuan->nomor_pengajuan}] - {$namaTeller}",
                'debit' => $pengajuan->nominal,
                'kredit' => 0,
                'tanggal' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} sebesar Rp " . number_format($pengajuan->nominal, 0, ',', '.') . " BERHASIL DISETUJUI! Kas operasional loket otomatis bertambah.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses persetujuan pengajuan kas: ' . $e->getMessage());
        }
    }

    /**
     * Supervisor / Accounting Menolak Pengajuan Kas
     */
    public function tolak(Request $request, $id)
    {
        $pengajuan = DB::table('pengajuan_kas')->where('id', $id)->first();
        if (!$pengajuan) {
            return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
        }

        DB::table('pengajuan_kas')->where('id', $id)->update([
            'status' => 'ditolak',
            'disetujui_oleh' => Auth::id(),
            'catatan_spv' => $request->catatan_spv ?? 'Ditolak: Kas loket dinilai masih mencukupi atau anggaran belum tersedia.',
            'updated_at' => now(),
        ]);

        return back()->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah DITOLAK.");
    }

    /**
     * Fitur Atur / Input Kas Awal Operasional Hari Ini
     */
    public function setKasAwal(Request $request)
    {
        $request->validate([
            'saldo_awal' => 'required|numeric|min:0',
        ]);

        $today = Carbon::today()->toDateString();
        $saldoAwal = $request->saldo_awal;

        DB::beginTransaction();
        try {
            $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->lockForUpdate()->first();

            if ($brankasHariIni) {
                $total = $saldoAwal + $brankasHariIni->masuk - $brankasHariIni->keluar;
                DB::table('brankas')->where('id', $brankasHariIni->id)->update([
                    'saldo_awal' => $saldoAwal,
                    'total' => $total,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('brankas')->insert([
                    'tanggal' => $today,
                    'saldo_awal' => $saldoAwal,
                    'masuk' => 0,
                    'keluar' => 0,
                    'total' => $saldoAwal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update opening_balance di daily_reports jika teller sudah buat draft
            DB::table('daily_reports')
                ->whereDate('report_date', $today)
                ->where('status', '!=', 'approved')
                ->update(['opening_balance' => $saldoAwal]);

            DB::commit();

            return back()->with('success', "Kas Awal operasional hari ini berhasil ditetapkan sebesar Rp " . number_format($saldoAwal, 0, ',', '.') . "!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menetapkan Kas Awal: ' . $e->getMessage());
        }
    }
}
