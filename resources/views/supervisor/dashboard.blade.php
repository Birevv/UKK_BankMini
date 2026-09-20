<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor - E-Bank</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR SUPERVISOR -->
    <nav class="bg-indigo-700 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">E-Bank | Supervisor</div>
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/supervisor/dashboard') }}" class="text-white font-bold underline decoration-2 underline-offset-4">Dashboard</a>
                    <a href="{{ url('/supervisor/laporan') }}" class="text-indigo-200 hover:text-white transition font-medium">Laporan Transaksi</a>

                    <!-- PROFIL DROPDOWN SUPERVISOR -->
                    <div class="relative ml-3">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-indigo-800 hover:bg-indigo-900 py-1.5 px-3 rounded-full transition border border-indigo-600">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- ISI DROPDOWN -->
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">SUPERVISOR</span>
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

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <p class="text-slate-500 text-sm font-bold mb-1">Total Nasabah Terdaftar</p>
                <p class="text-3xl font-bold text-indigo-700">{{ $totalNasabah }} <span class="text-lg font-normal text-slate-500">Siswa</span></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <p class="text-slate-500 text-sm font-bold mb-1">Akumulasi Seluruh Saldo</p>
                <p class="text-3xl font-bold text-indigo-700">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
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
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-center">Aksi ACC</th>
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
                                <a href="{{ url('/supervisor/approve/'.$t->id) }}" onclick="return confirm('Setujui transaksi ini?')" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-xs transition">
                                    Setujui (ACC)
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
