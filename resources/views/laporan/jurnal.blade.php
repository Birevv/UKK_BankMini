<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Akuntansi Keuangan - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR KONSISTEN DENGAN ADMIN (bg-slate-800) -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <span class="text-xl font-bold tracking-wider">LKM Mitra Siswa Abadi</span>
                    <span class="bg-blue-600 text-white text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Laporan Akuntansi</span>
                </div>

                <div class="flex items-center space-x-5 text-sm">
                    @if(str_contains($role, 'admin'))
                        <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                        <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium">Nasabah</a>
                    @else
                        <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                        <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium">Daily Report</a>
                    @endif

                    <a href="{{ url('/laporan/jurnal') }}" class="text-white font-bold underline decoration-2 underline-offset-4 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Jurnal Akuntansi
                    </a>
                    <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium">Saldo Produk</a>
                    <a href="{{ url('/laporan/tahunan') }}" class="text-slate-300 hover:text-white transition font-medium">Laporan Tahunan</a>
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
        <!-- HEADER & EXPORT ACTIONS -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Buku Jurnal Umum Akuntansi Bank Mini
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Pencatatan mutasi kas debit dan kredit per transaksi nasabah beserta kode sandi transaksi perbankan.
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                   class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Ekspor Excel (.xls)
                </a>

                <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank"
                   class="bg-blue-800 hover:bg-blue-900 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Dokumen (Print)
                </a>
            </div>
        </div>

        <!-- 3 KARTU BALANCE METER -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Debit (Kas Masuk)</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Penerimaan setoran tabungan siswa</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Kredit (Kas Keluar)</p>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalKredit, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Pengeluaran penarikan & administrasi</p>
            </div>

            <div class="p-5 rounded-xl border {{ $isBalanced ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }} shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider">Status Keseimbangan (Audit)</p>
                    <span class="text-xs font-black px-2 py-0.5 rounded {{ $isBalanced ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                        {{ $isBalanced ? 'SEIMBANG' : 'DEFISIT / SELISIH' }}
                    </span>
                </div>
                <p class="text-2xl font-black mt-1">
                    {{ $isBalanced ? 'Rp 0 (Klop)' : 'Rp ' . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') }}
                </p>
                <p class="text-xs mt-1 {{ $isBalanced ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $isBalanced ? 'Jurnal memenuhi standar pembukuan berpasangan.' : 'Perhatian: Terdapat anomali selisih saldo pembukuan.' }}
                </p>
            </div>
        </div>

        <!-- FORM FILTER WAJIB (Sesuai QnA: Tanggal, Nama Teller, Nama Nasabah, Rekening) -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center">
                <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Audit Akuntansi Wajib
            </h2>
            <form action="{{ url('/laporan/jurnal') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                           class="w-full border border-slate-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                           class="w-full border border-slate-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 mb-1">Nama Teller / Avalis</label>
                    <input type="text" name="nama_teller" value="{{ request('nama_teller') }}" placeholder="Contoh: Teller 1"
                           class="w-full border border-slate-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 mb-1">Nama Siswa / Nasabah</label>
                    <input type="text" name="nama_nasabah" value="{{ request('nama_nasabah') }}" placeholder="Contoh: Budi"
                           class="w-full border border-slate-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 mb-1">No. Rekening / NIS</label>
                    <input type="text" name="rekening" value="{{ request('rekening') }}" placeholder="Contoh: RPL-001"
                           class="w-full border border-slate-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    @if(request()->hasAny(['tanggal_mulai', 'tanggal_akhir', 'nama_teller', 'nama_nasabah', 'rekening']))
                    <a href="{{ url('/laporan/jurnal') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-4 py-2 rounded-lg text-xs transition">
                        Reset Filter
                    </a>
                    @endif
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-6 py-2 rounded-lg text-xs transition flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter Data Jurnal
                    </button>
                </div>
            </form>
        </div>

        <!-- TABEL JURNAL AKUNTANSI -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center text-xs">
                <span class="font-bold text-slate-700">Daftar Baris Jurnal Pembukuan</span>
                <span class="text-slate-500 font-mono">Total Data: {{ count($jurnal) }} entri</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-10 text-center">No</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">No. Rekening</th>
                            <th class="p-3">Nama Nasabah</th>
                            <th class="p-3">Keterangan / Sandi</th>
                            <th class="p-3">Teller</th>
                            <th class="p-3 text-right">Debit (Rp)</th>
                            <th class="p-3 text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($jurnal as $idx => $j)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="p-3 font-mono text-slate-600">{{ date('d/m/Y', strtotime($j->tanggal)) }}</td>
                            <td class="p-3 font-mono font-bold text-blue-900">{{ $j->no_rekening ?? $j->nis ?? '-' }}</td>
                            <td class="p-3 font-bold text-slate-800">{{ $j->nama_siswa ?? '-' }}</td>
                            <td class="p-3 text-slate-700">
                                <span class="block">{{ $j->keterangan }}</span>
                                @if($j->kode_sandi)
                                    <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">Sandi [{{ $j->kode_sandi }}]</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600">{{ $j->nama_teller ?? 'Teller Loket' }}</td>
                            <td class="p-3 text-right font-mono font-bold text-emerald-700">
                                {{ $j->debit > 0 ? 'Rp ' . number_format($j->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-rose-700">
                                {{ $j->kredit > 0 ? 'Rp ' . number_format($j->kredit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">Tidak ada entri jurnal yang ditemukan sesuai filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="6" class="p-3 text-right uppercase tracking-wider text-xs">Total Akumulasi:</td>
                            <td class="p-3 text-right font-mono text-emerald-700 text-sm">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-rose-700 text-sm">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
