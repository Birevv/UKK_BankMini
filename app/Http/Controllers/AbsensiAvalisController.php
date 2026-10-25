<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiAvalisController extends Controller
{
    /**
     * Tampilkan Halaman Absensi Avalis Bergaya Digital Excel
     */
    public function index(Request $request)
    {
        $query = DB::table('absensi_avalis');

        // Filter Pencarian (Nama / Kelas / No WA)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('kelas', 'like', "%{$q}%")
                    ->orWhere('no_wa', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%");
            });
        }

        // Filter Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter Shift
        if ($request->filled('shift')) {
            $query->where('shift_jaga', 'like', "%{$request->shift}%");
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensi = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // Statistik Formula Excel (fx)
        $totalAvalis   = DB::table('absensi_avalis')->count();
        $totalHadir    = DB::table('absensi_avalis')->where('status', 'Hadir')->count();
        $totalIzin     = DB::table('absensi_avalis')->where('status', 'Izin')->count();
        $totalSakit    = DB::table('absensi_avalis')->where('status', 'Sakit')->count();
        $totalShiftPagi = DB::table('absensi_avalis')->where('shift_jaga', 'like', '%Pagi%')->count();
        $totalShiftSiang = DB::table('absensi_avalis')->where('shift_jaga', 'like', '%Siang%')->count();

        $role = Auth::user()->role ?? 'admin';

        return view('avalis.index', compact(
            'absensi',
            'totalAvalis',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalShiftPagi',
            'totalShiftSiang',
            'role'
        ));
    }

    /**
     * Simpan Absensi Avalis Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'shift_jaga' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'status' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::table('absensi_avalis')->insert([
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'no_wa' => $request->no_wa,
            'shift_jaga' => $request->shift_jaga,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::user()->nama_petugas ?? 'Petugas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data absensi Avalis berhasil ditambahkan ke lembar kerja Excel!');
    }

    /**
     * Update Absensi Avalis
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'shift_jaga' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'status' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::table('absensi_avalis')->where('id', $id)->update([
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'no_wa' => $request->no_wa,
            'shift_jaga' => $request->shift_jaga,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Baris absensi Avalis berhasil diperbarui!');
    }

    /**
     * Hapus Absensi Avalis
     */
    public function destroy($id)
    {
        DB::table('absensi_avalis')->where('id', $id)->delete();
        return back()->with('success', 'Baris data absensi berhasil dihapus!');
    }

    /**
     * Ekspor Data Absensi Avalis ke File Excel (.csv format kompatibel MS Excel)
     */
    public function exportCsv(Request $request)
    {
        $query = DB::table('absensi_avalis');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('shift')) {
            $query->where('shift_jaga', 'like', "%{$request->shift}%");
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        $filename = 'Absensi_Avalis_Digital_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['No', 'Tanggal', 'Nama Avalis', 'Kelas', 'No WhatsApp', 'Shift Jaga', 'Status Kehadiran', 'Keterangan / Tugas', 'Petugas Pencatat'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            // Menambahkan UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header kolom Excel
            fputcsv($file, $columns, ';');

            $no = 1;
            foreach ($data as $row) {
                fputcsv($file, [
                    $no++,
                    $row->tanggal,
                    $row->nama,
                    $row->kelas,
                    "'" . $row->no_wa, // Menggunakan prefix petik agar angka 0 di awal nomor tidak hilang di Excel
                    $row->shift_jaga,
                    $row->status,
                    $row->keterangan ?? '-',
                    $row->created_by ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
