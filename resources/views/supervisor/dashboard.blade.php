<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR SUPERVISOR -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-5 text-sm">
                    <a href="{{ url('/supervisor/dashboard') }}" class="text-white font-medium underline decoration-2 underline-offset-4">Dashboard</a>
                    <a href="{{ url('/supervisor/laporan') }}" class="text-slate-300 hover:text-white transition font-medium">Laporan Transaksi</a>
                    <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium">Daily Report</a>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center">
                        Pengajuan Kas
                        @if(($pendingPengajuanKasCount ?? 0) > 0)
                            <span class="ml-1 bg-amber-500 text-slate-900 text-[10px] font-black px-1.5 py-0.2 rounded-full">{{ $pendingPengajuanKasCount }}</span>
                        @endif
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

                        <!-- ISI DROPDOWN -->
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
        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Pengawasan</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">
                        <span>Total Nasabah</span>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">{{ $totalNasabah }} <span class="text-sm font-semibold text-slate-500">Siswa</span></p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Buku rekening tabungan aktif</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">
                        <span>Total Saldo Nasabah</span>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Akumulasi simpanan siswa</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">
                        <span>Kas Loket Hari Ini</span>
                        @if(($kasTersedia ?? 0) <= 0)
                            <span class="bg-rose-100 text-rose-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Habis</span>
                        @elseif(($kasTersedia ?? 0) < 500000)
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Menipis</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Aman</span>
                        @endif
                    </div>
                    <p class="text-2xl font-black {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-slate-800' }}">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="text-xs text-slate-500 mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span>Kas Awal: Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}</span>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-indigo-600 hover:underline font-bold text-[11px]">Kelola &rarr;</a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border {{ ($pendingPengajuanKasCount ?? 0) > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200' }} flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">
                        <span>Pengajuan Kas</span>
                        @if(($pendingPengajuanKasCount ?? 0) > 0)
                            <span class="bg-amber-500 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded-full animate-pulse">{{ $pendingPengajuanKasCount }} Menunggu</span>
                        @else
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full">0 Pending</span>
                        @endif
                    </div>
                    <p class="text-2xl font-black text-slate-800">{{ $pendingPengajuanKasCount ?? 0 }} <span class="text-sm font-semibold text-slate-500">Permintaan</span></p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="inline-flex items-center text-xs font-bold text-amber-700 hover:text-amber-800">
                        Buka Permintaan Kas Teller
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="font-bold text-slate-700">Menunggu Persetujuan (Pending)</h2>
            </div>
            <table class="min-w-full bg-white text-left">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="py-3 px-4 font-bold border-b border-slate-200">Waktu</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200">Nasabah (NIS)</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200">Jenis</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200">Nominal</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    @forelse ($transaksi as $t)
                        @if($t->status == 'pending')
                        <tr class="hover:bg-slate-50 border-b border-slate-100">
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-bold">{{ $t->nama_siswa }} ({{ $t->nis }})</td>
                            <td class="py-3 px-4 uppercase font-bold text-xs">{{ $t->jenis_transaksi }}</td>
                            <td class="py-3 px-4 font-bold">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ url('/supervisor/approve/'.$t->id) }}" onclick="return confirm('Setujui transaksi ini?')" class="inline-block bg-slate-800 hover:bg-slate-900 text-white font-bold py-1 px-3 rounded text-xs transition">
                                    Setujui
                                </a>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada transaksi yang butuh persetujuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function toggleDropdown() { document.getElementById('profile-dropdown').classList.toggle('hidden'); }
        window.addEventListener('click', function(e) {
            if (!document.getElementById('profile-btn').contains(e.target) && !document.getElementById('profile-dropdown').contains(e.target)) {
                document.getElementById('profile-dropdown').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
