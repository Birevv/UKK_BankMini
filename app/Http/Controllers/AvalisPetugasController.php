<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AvalisPetugasController extends Controller
{
    /**
     * Tampilkan Manajemen Master Data Petugas Avalis / Teller
     */
    public function index(Request $request)
    {
        $query = DB::table('avalis')
            ->leftJoin('users', 'avalis.user_id', '=', 'users.id')
            ->select('avalis.*', 'users.username as akun_username', 'users.nama_petugas as nama_akun_petugas');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('avalis.nama', 'LIKE', "%{$q}%")
                    ->orWhere('avalis.kelas', 'LIKE', "%{$q}%")
                    ->orWhere('avalis.no_wa', 'LIKE', "%{$q}%");
            });
        }

        $avalisList = $query->orderBy('avalis.nama', 'asc')->get();

        // Ambil daftar users yang bisa dihubungkan (role: teller atau belum punya avalis)
        $usersTeller = DB::table('users')->where('role', 'teller')->get();

        $role = strtolower(Auth::user()->role ?? 'admin');

        return view('avalis.petugas', compact('avalisList', 'usersTeller', 'role'));
    }

    /**
     * Tambah 1 Petugas Avalis Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $avalisId = DB::table('avalis')->insertGetId([
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'no_wa' => $request->no_wa,
            'user_id' => $request->user_id,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hubungkan otomatis ke lembar Absensi Avalis hari ini jika diminta
        if ($request->boolean('masuk_absensi_hari_ini')) {
            DB::table('absensi_avalis')->insert([
                'tanggal' => now()->toDateString(),
                'nama' => $request->nama,
                'kelas' => $request->kelas,
                'no_wa' => $request->no_wa,
                'shift_jaga' => $request->shift_jaga ?? 'Shift Pagi (07.30 - 11.30)',
                'status' => 'Hadir',
                'keterangan' => 'Petugas Avalis Terdaftar (Auto-Sync)',
                'created_by' => Auth::user()->nama_petugas ?? 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', "Petugas Avalis '{$request->nama}' berhasil ditambahkan!");
    }

    /**
     * Tambah Banyak Identitas Teller Sekaligus (Bulk Add)
     * Format baris: Nama, Kelas, No WA
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'data_bulk' => 'required|string',
        ]);

        $lines = explode("\n", str_replace("\r", "", $request->data_bulk));
        $sukses = 0;
        $gagal = 0;

        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Pisahkan berdasarkan koma (,) atau titik-koma (;)
                $parts = preg_split('/[,;]/', $line);
                if (count($parts) >= 2) {
                    $nama = trim($parts[0]);
                    $kelas = trim($parts[1]);
                    $no_wa = isset($parts[2]) ? trim($parts[2]) : '-';

                    if (!empty($nama) && !empty($kelas)) {
                        DB::table('avalis')->insert([
                            'nama' => $nama,
                            'kelas' => $kelas,
                            'no_wa' => $no_wa,
                            'status' => 'aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Juga sinkronkan ke absensi hari ini jika dicentang
                        if ($request->boolean('sync_ke_absensi')) {
                            DB::table('absensi_avalis')->insert([
                                'tanggal' => now()->toDateString(),
                                'nama' => $nama,
                                'kelas' => $kelas,
                                'no_wa' => $no_wa,
                                'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
                                'status' => 'Hadir',
                                'keterangan' => 'Piket Avalis Terjadwal (Bulk Import)',
                                'created_by' => Auth::user()->nama_petugas ?? 'Admin',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        $sukses++;
                    } else {
                        $gagal++;
                    }
                } else {
                    $gagal++;
                }
            }

            DB::commit();
            return back()->with('success', "Proses Bulk Berhasil! {$sukses} identitas teller berhasil ditambahkan" . ($gagal > 0 ? " ({$gagal} baris dilewati karena format tidak lengkap)." : "."));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses bulk data: ' . $e->getMessage());
        }
    }

    /**
     * Hubungkan Avalis Terdaftar ke Lembar Absensi Hari Ini (Auto-Presensi)
     */
    public function syncKeAbsensi(Request $request, $id)
    {
        $avalis = DB::table('avalis')->where('id', $id)->first();
        if (!$avalis) {
            return back()->with('error', 'Petugas tidak ditemukan!');
        }

        $shift = $request->input('shift', 'Shift Pagi (07.30 - 11.30)');
        $today = now()->toDateString();

        // Cek apakah sudah absen hari ini di shift tersebut
        $exists = DB::table('absensi_avalis')
            ->where('nama', $avalis->nama)
            ->whereDate('tanggal', $today)
            ->where('shift_jaga', $shift)
            ->first();

        if ($exists) {
            return back()->with('error', "Petugas '{$avalis->nama}' sudah terdata di absensi hari ini pada {$shift}.");
        }

        DB::table('absensi_avalis')->insert([
            'tanggal' => $today,
            'nama' => $avalis->nama,
            'kelas' => $avalis->kelas,
            'no_wa' => $avalis->no_wa,
            'shift_jaga' => $shift,
            'status' => 'Hadir',
            'keterangan' => 'Standby Petugas Loket (Tersinkronisasi)',
            'created_by' => Auth::user()->nama_petugas ?? 'Petugas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Petugas '{$avalis->nama}' berhasil dimasukkan ke lembar absensi digital hari ini!");
    }

    public function destroy($id)
    {
        DB::table('avalis')->where('id', $id)->delete();
        return back()->with('success', 'Data petugas Avalis berhasil dihapus.');
    }
}
