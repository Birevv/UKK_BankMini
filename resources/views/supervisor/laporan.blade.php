<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Supervisor E-Bank</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR SUPERVISOR -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-5 text-sm">
                    <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                    <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Daily Report
                    </a>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pengajuan Kas
                    </a>
                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium">Jurnal</a>
                    <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium">Produk</a>
                    <a href="{{ url('/laporan/tahunan') }}" class="text-slate-300 hover:text-white transition font-medium">Tahunan</a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium">Data Teller</a>
                    <a href="{{ url('/supervisor/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium">Absensi</a>

                    <!-- PROFIL DROPDOWN SUPERVISOR -->
                    <div class="relative ml-3">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">SUPERVISOR</span>
                            </div>
                            <a href="{{ url('/supervisor/profil') }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Lihat & Edit Profil Saya
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 flex items-center font-bold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Laporan Transaksi Hari Ini</h1>
                <p class="text-slate-600 text-sm mt-1">Daftar riwayat transaksi nasabah khusus pada <strong>{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</strong>.</p>
            </div>
            <!-- Tombol Cetak LKM -->
            <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="bg-slate-800 hover:bg-slate-900 text-white py-2.5 px-5 rounded-lg text-sm font-bold shadow-sm transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan LKM (Harian)
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                        <tr>
                            <th class="py-4 px-6 font-bold text-sm">Waktu Transaksi</th>
                            <th class="py-4 px-6 font-bold text-sm">Nama Nasabah & NIS</th>
                            <th class="py-4 px-6 font-bold text-sm">Jenis Transaksi</th>
                            <th class="py-4 px-6 font-bold text-sm">Nominal</th>
                            <th class="py-4 px-6 font-bold text-sm text-center">Buku / Slip</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-sm">
                        @forelse ($transaksi as $t)
                        <tr class="hover:bg-slate-50 border-b border-slate-100 transition">
                            <td class="py-4 px-6 text-slate-500">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }} WIB</td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ $t->nama_siswa }}
                                <div class="text-xs text-slate-500 font-normal mt-0.5">{{ $t->nis }}</div>
                            </td>
                            <td class="py-4 px-6 uppercase font-bold text-xs">{{ $t->jenis_transaksi }}</td>
                            <td class="py-4 px-6 font-bold {{ $t->jenis_transaksi == 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $t->jenis_transaksi == 'setor' ? '+' : '-' }} Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap space-x-1">
                                <a href="{{ url('/buku-tabungan/'.$t->nasabah_id) }}" target="_blank" class="bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold py-1 px-2.5 rounded text-xs transition border border-blue-200">
                                    Buku Tabungan
                                </a>
                                @if($t->jenis_transaksi === 'setor')
                                    <a href="{{ url('/teller/slip-setor/'.$t->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-1 px-2 rounded text-xs transition border border-slate-300">
                                        Slip
                                    </a>
                                @else
                                    <a href="{{ url('/teller/slip-tarik/'.$t->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-1 px-2 rounded text-xs transition border border-slate-300">
                                        Slip
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada transaksi hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function toggleDropdown() {
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        }
        window.addEventListener('click', function(e) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
