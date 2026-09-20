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

        // ALGORITMA SORTING PINTAR UNTUK ADMIN (Tingkat -> Jurusan -> SubKelas -> NIS)
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

            // Menggabungkan skor dan NIS (NIS diberi bantalan 10 digit agar urut sempurna)
            return sprintf('%02d-%02d-%02d-%010s', $grade, $jurusan, $sub, $item->nis);
        })->values();

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

    // FUNGSI IMPORT DARI EXCEL (CSV)
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
                if ($header) { $header = false; continue; }

                if(!isset($row[0]) || !isset($row[1])) continue;

                $nis = $row[0];
                $nama = $row[1];
                $kelas = isset($row[2]) ? $row[2] : '-';
                $jurusan = isset($row[3]) ? $row[3] : '-';

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
            return back()->with('success', "Import Selesai! $sukses data masuk, $gagal data diabaikan (NIS kembar/kosong).");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal impor data: Format file tidak sesuai.');
        }
    }

    // FUNGSI DOWNLOAD TEMPLATE EXCEL (CSV)
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
            fputcsv($file, ['17764', 'Bima Revan Saputra', 'XII RPL 2', 'Rekayasa Perangkat Lunak']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
