<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    public function index()
    {
        $totalNasabah = DB::table('nasabah')->count();
        $totalSaldo = DB::table('nasabah')->sum('saldo');

        $transaksi = DB::table('transaksi')
                        ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                        ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis')
                        ->orderBy('transaksi.created_at', 'desc')
                        ->get();

        return view('supervisor.dashboard', compact('totalNasabah', 'totalSaldo', 'transaksi'));
    }

    public function laporan()
    {
        $transaksi = DB::table('transaksi')
                        ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                        ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis')
                        ->whereDate('transaksi.created_at', Carbon::today())
                        ->orderBy('transaksi.created_at', 'desc')
                        ->get();

        return view('supervisor.laporan', compact('transaksi'));
    }

    public function approve($id)
    {
        DB::table('transaksi')->where('id', $id)->update([
            'status' => 'sukses'
        ]);

        return back()->with('success', 'Transaksi berhasil disetujui oleh Supervisor!');
    }

    public function cetakHarian()
    {
        $hariIni = Carbon::today();

        $brankas = DB::table('brankas')->where('tanggal', $hariIni->toDateString())->first();
        if (!$brankas) {
            $brankas = (object) [
                'saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'total' => 0
            ];
        }

        $totalAdmin = DB::table('transaksi')
                        ->whereDate('created_at', $hariIni)
                        ->where('jenis_transaksi', 'admin')
                        ->sum('nominal');

        $transaksiHariIni = DB::table('transaksi')
                        ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                        ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
                        ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis', 'users.nama_petugas')
                        ->whereDate('transaksi.created_at', $hariIni)
                        ->orderBy('transaksi.created_at', 'asc')
                        ->get();

        $petugasTeller = $transaksiHariIni->pluck('nama_petugas')->filter()->unique();

        return view('supervisor.cetak-harian', compact('brankas', 'totalAdmin', 'transaksiHariIni', 'petugasTeller'));
    }

    public function profil()
    {
        $user = Auth::user();
        return view('supervisor.profil', compact('user'));
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
        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
