<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\TerbilangHelper;

class LaporanKeuanganController extends Controller
{
    /**
     * Laporan Keuangan Jurnal Akuntansi dengan Filter Wajib (Tanggal, Teller, Nasabah, Rekening)
     */
    public function jurnalAkuntansi(Request $request)
    {
        $query = DB::table('jurnal')
            ->leftJoin('transaksi', 'jurnal.transaksi_id', '=', 'transaksi.id')
            ->leftJoin('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
            ->leftJoin('users as teller', 'transaksi.petugas_id', '=', 'teller.id')
            ->select(
                'jurnal.*',
                'transaksi.jenis_transaksi',
                'transaksi.kode_sandi',
                'nasabah.nama_siswa',
                'nasabah.nis',
                'nasabah.no_rekening',
                'teller.nama_petugas as nama_teller'
            );

        // Filter Rentang Tanggal (Wajib sesuai QnA 1 & 4)
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('jurnal.tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('jurnal.tanggal', '<=', $request->tanggal_akhir);
        }

        // Filter Nama Teller
        if ($request->filled('nama_teller')) {
            $query->where('teller.nama_petugas', 'LIKE', "%{$request->nama_teller}%");
        }

        // Filter Nama Nasabah
        if ($request->filled('nama_nasabah')) {
            $query->where('nasabah.nama_siswa', 'LIKE', "%{$request->nama_nasabah}%");
        }

        // Filter Nomor Rekening
        if ($request->filled('rekening')) {
            $rek = $request->rekening;
            $query->where(function($sub) use ($rek) {
                $sub->where('nasabah.no_rekening', 'LIKE', "%{$rek}%")
                    ->orWhere('nasabah.nis', 'LIKE', "%{$rek}%");
            });
        }

        $jurnal = $query->orderBy('jurnal.tanggal', 'desc')->orderBy('jurnal.id', 'desc')->get();

        $totalDebit = $jurnal->sum('debit');
        $totalKredit = $jurnal->sum('kredit');
        $isBalanced = ($totalDebit == $totalKredit);

        $role = strtolower(Auth::user()->role ?? 'accounting');

        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportExcelJurnal($jurnal, $totalDebit, $totalKredit);
        }

        if ($request->has('export') && $request->export === 'print') {
            return view('laporan.cetak-jurnal', compact('jurnal', 'totalDebit', 'totalKredit', 'isBalanced'));
        }

        return view('laporan.jurnal', compact('jurnal', 'totalDebit', 'totalKredit', 'isBalanced', 'role'));
    }

    /**
     * Laporan Saldo Simpanan Per Produk (Tabungan Siswa vs Tabungan Guru/Karyawan)
     */
    public function saldoPerProduk(Request $request)
    {
        $produk = DB::table('nasabah')
            ->select('kategori', DB::raw('count(*) as total_nasabah'), DB::raw('sum(saldo) as total_saldo'))
            ->groupBy('kategori')
            ->get();

        $siswaList = DB::table('nasabah')->where('kategori', 'siswa')->get();
        $guruList = DB::table('nasabah')->where('kategori', 'guru_karyawan')->get();

        $totalSiswaSaldo = $siswaList->sum('saldo');
        $totalGuruSaldo = $guruList->sum('saldo');
        $grandTotal = $totalSiswaSaldo + $totalGuruSaldo;

        if ($request->has('export') && $request->export === 'print') {
            return view('laporan.cetak-saldo-produk', compact('siswaList', 'guruList', 'totalSiswaSaldo', 'totalGuruSaldo', 'grandTotal'));
        }

        $role = strtolower(Auth::user()->role ?? 'accounting');

        return view('laporan.saldo-produk', compact(
            'siswaList',
            'guruList',
            'totalSiswaSaldo',
            'totalGuruSaldo',
            'grandTotal',
            'role'
        ));
    }

    /**
     * Laporan Tahunan Rekapitulasi Kas & Transaksi Per Bulan
     */
    public function laporanTahunan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $rekapBulanan = DB::table('transaksi')
            ->select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(CASE WHEN jenis_transaksi IN ("setor", "koreksi_tarik") THEN nominal ELSE 0 END) as total_setor'),
                DB::raw('SUM(CASE WHEN jenis_transaksi IN ("tarik", "koreksi_setor") THEN nominal ELSE 0 END) as total_tarik'),
                DB::raw('SUM(CASE WHEN jenis_transaksi = "admin" THEN nominal ELSE 0 END) as total_admin'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->whereYear('created_at', $tahun)
            ->where('status', '!=', 'dibatalkan')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('bulan', 'asc')
            ->get();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($request->has('export') && $request->export === 'print') {
            return view('laporan.cetak-tahunan', compact('rekapBulanan', 'namaBulan', 'tahun'));
        }

        $role = strtolower(Auth::user()->role ?? 'accounting');

        return view('laporan.tahunan', compact('rekapBulanan', 'namaBulan', 'tahun', 'role'));
    }

    /**
     * Ekspor Format Spreadsheet Excel (.xls yang berformat rapi, bukan CSV mentah)
     */
    private function exportExcelJurnal($jurnal, $totalDebit, $totalKredit)
    {
        $filename = 'Jurnal_Akuntansi_Bank_Mini_' . date('Y-m-d_His') . '.xls';

        $output = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $output .= '<head><meta charset="utf-8">';
        $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Jurnal Akuntansi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $output .= '<style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
            th { background-color: #1e3a8a; color: #ffffff; border: 1px solid #000; padding: 8px; text-align: center; }
            td { border: 1px solid #94a3b8; padding: 6px; font-size: 11px; }
            .header-title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 10px; }
            .currency { text-align: right; }
        </style>';
        $output .= '</head><body>';

        $output .= '<table>';
        $output .= '<tr><td colspan="8" class="header-title">LKM MITRA SISWA ABADI - JURNAL AKUNTANSI BANK MINI</td></tr>';
        $output .= '<tr><td colspan="8" style="text-align:center; font-size:11px; color:#555;">Dicetak pada: ' . date('d/m/Y H:i') . ' WIB</td></tr>';
        $output .= '<tr><td colspan="8"></td></tr>';

        $output .= '<tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Rekening</th>
            <th>Nama Nasabah</th>
            <th>Keterangan / Sandi</th>
            <th>Teller / Avalis</th>
            <th>Debit (Rp)</th>
            <th>Kredit (Rp)</th>
        </tr>';

        $no = 1;
        foreach ($jurnal as $j) {
            $output .= '<tr>';
            $output .= '<td style="text-align:center;">' . $no++ . '</td>';
            $output .= '<td style="text-align:center;">' . date('d/m/Y', strtotime($j->tanggal)) . '</td>';
            $output .= '<td style="mso-number-format:\'@\';">' . ($j->no_rekening ?? $j->nis ?? '-') . '</td>';
            $output .= '<td>' . ($j->nama_siswa ?? '-') . '</td>';
            $output .= '<td>' . $j->keterangan . '</td>';
            $output .= '<td>' . ($j->nama_teller ?? 'Teller') . '</td>';
            $output .= '<td class="currency">' . number_format($j->debit, 0, ',', '.') . '</td>';
            $output .= '<td class="currency">' . number_format($j->kredit, 0, ',', '.') . '</td>';
            $output .= '</tr>';
        }

        $output .= '<tr style="font-weight:bold; background-color:#f1f5f9;">';
        $output .= '<td colspan="6" style="text-align:right;">TOTAL:</td>';
        $output .= '<td class="currency">Rp ' . number_format($totalDebit, 0, ',', '.') . '</td>';
        $output .= '<td class="currency">Rp ' . number_format($totalKredit, 0, ',', '.') . '</td>';
        $output .= '</tr>';

        $output .= '</table>';
        $output .= '</body></html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
