<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR TELLER DENGAN PROFIL DROPDOWN -->
    <nav class="bg-emerald-600 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">LKM Mitra Siswa Abadi</div>

                <div class="flex items-center space-x-6">
                    <a href="{{ url('/teller/dashboard') }}" class="text-white font-bold underline decoration-2 underline-offset-4">Dashboard</a>
                    <a href="{{ url('/teller/riwayat') }}" class="text-emerald-100 hover:text-white transition font-medium">Riwayat Saya</a>

                    <!-- PROFIL DROPDOWN -->
                    <div class="relative ml-3">
                        <div>
                            <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-3 focus:outline-none bg-emerald-700 hover:bg-emerald-800 py-1.5 px-3 rounded-full transition border border-emerald-500">
                                @if(Auth::user()->foto)
                                    <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-8 h-8 rounded-full object-cover border-2 border-emerald-400">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-sm border-2 border-emerald-300">
                                        {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'P', 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->nama_petugas }}</span>
                                <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>

                        <!-- ISI DROPDOWN -->
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <a href="{{ url('/teller/profil') }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
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
        <!-- Notifikasi Ceklis Hijau Sesuai Usulan Teller -->
        <div class="bg-emerald-100 border-l-4 border-emerald-600 text-emerald-800 p-4 mb-6 rounded shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-bold text-lg">TRANSAKSI BERHASIL TERSIMPAN!</p>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Cari Nasabah</h1>
            <p class="text-slate-600 mt-1">Ketikkan <strong class="text-emerald-600">NIS</strong> atau <strong class="text-emerald-600">Nama Siswa</strong> untuk mulai melayani transaksi.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-8">
            <!-- Form Pencarian dengan Auto-focus -->
            <form action="{{ url('/teller/dashboard') }}" method="GET" class="flex items-center space-x-4">
                <div class="flex-grow">
                    <!-- ATRIBUT AUTOFOCUS DITAMBAHKAN DI SINI -->
                    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Contoh: 17763 atau Dewi..." class="w-full px-4 py-3 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-900 font-medium text-lg" autocomplete="off" autofocus>
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-md transition shadow-sm text-lg">
                    Cari Nasabah
                </button>
                @if(request('cari'))
                    <a href="{{ url('/teller/dashboard') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-6 rounded-md transition text-lg">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="py-4 px-6 font-bold border-b border-slate-200">NIS</th>
                            <th class="py-4 px-6 font-bold border-b border-slate-200">Nama Lengkap</th>
                            <th class="py-4 px-6 font-bold border-b border-slate-200">Kelas & Jurusan</th>
                            <th class="py-4 px-6 font-bold border-b border-slate-200">Saldo</th>
                            <th class="py-4 px-6 font-bold border-b border-slate-200 text-center">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-sm">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="py-4 px-6 font-bold text-slate-500 font-mono text-base">{{ $n->nis }}</td>
                            <td class="py-4 px-6 font-bold text-slate-800 text-base">{{ $n->nama_siswa }}</td>
                            <td class="py-4 px-6">{{ $n->kelas }} - {{ $n->jurusan }}</td>
                            <td class="py-4 px-6 font-black text-emerald-600 text-base">Rp {{ number_format($n->saldo, 0, ',', '.') }}</td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ url('/teller/transaksi/'.$n->id) }}" class="inline-block bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold py-2 px-5 rounded-full transition text-sm shadow-sm">
                                    Proses Transaksi &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500 text-base">
                                @if(request('cari'))
                                    Nasabah dengan kata kunci <strong class="text-rose-500">"{{ request('cari') }}"</strong> tidak ditemukan.
                                @else
                                    Ketik NIS atau Scan QR Code untuk menampilkan data nasabah.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Script JavaScript untuk Dropdown -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
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
