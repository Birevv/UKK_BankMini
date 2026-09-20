<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NasabahController extends Controller
{
    public function index()
    {
        $nasabah = DB::table('nasabah')->get();
        return view('admin.data-nasabah', compact('nasabah'));
    }

    public function create()
    {
        return view('admin.tambah-nasabah');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:nasabah,nis',
            'nama_siswa' => 'required',
            'kelas' => 'required',
            'jurusan' => 'required',
        ]);

        DB::table('nasabah')->insert([
            'nis' => $request->nis,
            'nama_siswa' => $request->nama_siswa,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'saldo' => 0,
            'pin_keamanan' => Hash::make('123456'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/admin/data-nasabah')->with('success', 'Nasabah baru berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        return view('admin.edit-nasabah', compact('nasabah'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_siswa' => 'required',
            'kelas' => 'required',
            'jurusan' => 'required',
        ]);

        DB::table('nasabah')->where('id', $id)->update([
            'nama_siswa' => $request->nama_siswa,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'updated_at' => now(),
        ]);

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function destroy($id)
    {
        DB::table('nasabah')->where('id', $id)->delete();
        return redirect('/admin/data-nasabah')->with('success', 'Nasabah berhasil dihapus!');
    }

    // FUNGSI BARU: IMPORT DARI EXCEL (CSV)
    public function importCsv(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file_csv');
        $handle = fopen($file->getPathname(), "r");
        $header = true;
        $sukses = 0;
        $gagal = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if ($header) { $header = false; continue; } // Lewati baris judul kolom

                // Pastikan kolom tidak kosong (Minimal ada NIS dan Nama)
                if(!isset($row[0]) || !isset($row[1])) continue;

                $nis = $row[0];
                $nama = $row[1];
                $kelas = isset($row[2]) ? $row[2] : '-';
                $jurusan = isset($row[3]) ? $row[3] : '-';

                // Cek apakah NIS sudah terdaftar
                $exists = DB::table('nasabah')->where('nis', $nis)->first();
                if (!$exists && !empty($nis)) {
                    DB::table('nasabah')->insert([
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jurusan' => $jurusan,
                        'saldo' => 0,
                        'pin_keamanan' => Hash::make('123456'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sukses++;
                } else {
                    $gagal++;
                }
            }
            fclose($handle);
            DB::commit();
            return back()->with('success', "Proses Import Selesai! $sukses data baru berhasil ditambahkan. $gagal data diabaikan (NIS kembar/kosong).");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal impor data: Format file tidak sesuai. Pastikan menggunakan template yang disediakan.');
        }
    }

    // FUNGSI BARU: DOWNLOAD TEMPLATE EXCEL (CSV)
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_nasabah.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NIS', 'Nama Lengkap', 'Kelas (Contoh: XII RPL 1)', 'Jurusan (Contoh: Rekayasa Perangkat Lunak)'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['17763', 'Dewi Handayani Nur Halimah', 'XI RPL 1', 'Rekayasa Perangkat Lunak']);
            fputcsv($file, ['17764', 'Contoh Siswa Kedua', 'X TKJ 2', 'Teknik Komputer dan Jaringan']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
