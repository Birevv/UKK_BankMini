<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tahunan Transaksi & Kas - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR KONSISTEN DENGAN ADMIN (bg-slate-800) -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <span class="text-xl font-bold tracking-wider">LKM Mitra Siswa Abadi</span>
                    <span class="bg-blue-600 text-white text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Laporan Tahunan</span>
                </div>

                <div class="flex items-center space-x-5 text-sm">
                    @if(str_contains($role, 'admin'))
                        <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                        <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium">Nasabah</a>
                    @else
                        <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                        <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium">Daily Report</a>
                    @endif

                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium">Jurnal Akuntansi</a>
                    <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium">Saldo Produk</a>
                    <a href="{{ url('/laporan/tahunan') }}" class="text-white font-bold underline decoration-2 underline-offset-4 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Laporan Tahunan
                    </a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium">Data Teller</a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-rose-400 hover:text-rose-300 font-medium text-xs transition bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER & PILIHAN TAHUN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Laporan Rekapitulasi Tahunan Bank Mini (Tahun {{ $tahun }})
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Rekapitulasi total mutasi uang masuk, uang keluar, pendapatan administrasi buku, dan volume transaksi per bulan.
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <!-- FORM FILTER TAHUN -->
                <form action="{{ url('/laporan/tahunan') }}" method="GET" class="flex items-center space-x-2">
                    <select name="tahun" onchange="this.form.submit()" class="text-xs font-bold border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <a href="{{ url('/laporan/tahunan?tahun='.$tahun.'&export=print') }}" target="_blank"
                   class="bg-blue-800 hover:bg-blue-900 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan Tahunan
                </a>
            </div>
        </div>

        @php
            $totSetorTahun = $rekapBulanan->sum('total_setor');
            $totTarikTahun = $rekapBulanan->sum('total_tarik');
            $totAdminTahun = $rekapBulanan->sum('total_admin');
            $totJmlTahun   = $rekapBulanan->sum('jumlah_transaksi');
            $netKasTahun   = $totSetorTahun - $totTarikTahun;
        @endphp

        <!-- 4 KARTU REKAP TAHUNAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Setoran 1 Tahun</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totSetorTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi uang tunai disetor</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Penarikan 1 Tahun</p>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totTarikTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi uang tunai ditarik</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendapatan Biaya Admin</p>
                <p class="text-2xl font-black text-amber-600 mt-1">Rp {{ number_format($totAdminTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Biaya admin buku tabungan</p>
            </div>

            <div class="bg-blue-900 text-white p-5 rounded-xl shadow-md border border-blue-800">
                <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">Net Arus Kas (Setor - Tarik)</p>
                <p class="text-2xl font-black text-white mt-1">
                    {{ $netKasTahun >= 0 ? '+' : '' }}Rp {{ number_format($netKasTahun, 0, ',', '.') }}
                </p>
                <p class="text-xs text-blue-300 mt-1">{{ number_format($totJmlTahun, 0, ',', '.') }} Total Frekuensi Transaksi</p>
            </div>
        </div>

        <!-- TABEL REKAPITULASI BULAN KE BULAN -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="font-bold text-slate-800 text-sm">Rincian Perkembangan Kas & Transaksi Tiap Bulan</h2>
                <span class="text-xs font-mono font-bold text-slate-500">Tahun Buku {{ $tahun }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-12 text-center">Bulan</th>
                            <th class="p-3">Nama Bulan</th>
                            <th class="p-3 text-right">Total Setor (Rp)</th>
                            <th class="p-3 text-right">Total Tarik (Rp)</th>
                            <th class="p-3 text-right">Biaya Admin (Rp)</th>
                            <th class="p-3 text-right">Net Kas Masuk/Keluar (Rp)</th>
                            <th class="p-3 text-center">Volume Trx</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $row = $rekapBulanan->firstWhere('bulan', $m);
                                $setor = $row ? $row->total_setor : 0;
                                $tarik = $row ? $row->total_tarik : 0;
                                $adm   = $row ? $row->total_admin : 0;
                                $cnt   = $row ? $row->jumlah_transaksi : 0;
                                $net   = $setor - $tarik;
                            @endphp
                            <tr class="hover:bg-slate-50 {{ $m == date('n') && $tahun == date('Y') ? 'bg-blue-50/40 font-medium' : '' }}">
                                <td class="p-3 text-center font-mono text-slate-500">{{ $m }}</td>
                                <td class="p-3 font-bold text-slate-900">
                                    {{ $namaBulan[$m] }}
                                    @if($m == date('n') && $tahun == date('Y'))
                                        <span class="ml-1 text-[10px] font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">Bulan Berjalan</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-700">
                                    {{ $setor > 0 ? 'Rp ' . number_format($setor, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-rose-700">
                                    {{ $tarik > 0 ? 'Rp ' . number_format($tarik, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-amber-700">
                                    {{ $adm > 0 ? 'Rp ' . number_format($adm, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-black {{ $net >= 0 ? 'text-slate-800' : 'text-rose-600' }}">
                                    {{ ($setor > 0 || $tarik > 0) ? (($net >= 0 ? '+' : '') . 'Rp ' . number_format($net, 0, ',', '.')) : '-' }}
                                </td>
                                <td class="p-3 text-center font-mono text-slate-600">
                                    {{ $cnt > 0 ? number_format($cnt, 0, ',', '.') . ' Trx' : '-' }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="2" class="p-3 text-right uppercase tracking-wider text-xs">Total Setahun:</td>
                            <td class="p-3 text-right font-mono text-emerald-700 text-sm">Rp {{ number_format($totSetorTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-rose-700 text-sm">Rp {{ number_format($totTarikTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-amber-700 text-sm">Rp {{ number_format($totAdminTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-blue-900 text-sm">{{ $netKasTahun >= 0 ? '+' : '' }}Rp {{ number_format($netKasTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-center font-mono text-slate-800">{{ number_format($totJmlTahun, 0, ',', '.') }} Trx</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
