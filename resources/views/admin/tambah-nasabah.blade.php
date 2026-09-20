<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nasabah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR ADMIN (TEMA SLATE SERAGAM) -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">Administrator</div>
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium">Dashboard</a>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-white font-bold underline decoration-2 underline-offset-4">Data Nasabah</a>

                    <!-- PROFIL DROPDOWN ADMIN -->
                    <div class="relative ml-3">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">ADMINISTRATOR</span>
                            </div>
                            <a href="{{ url('/admin/profil') }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
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

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-800">Daftarkan Nasabah Baru</h1>
                <p class="text-slate-600 text-sm mt-1">Masukkan data siswa yang akan dijadikan nasabah bank mini.</p>
            </div>

            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded shadow-sm text-sm font-bold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/admin/tambah-nasabah') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nomor Induk Siswa (NIS)</label>
                    <input type="text" name="nis" placeholder="Contoh: 17799" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa" placeholder="Contoh: Bima Revan Saputra" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kelas Siswa</label>
                    <input type="text" name="kelas" placeholder="Contoh: XII RPL 2" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jurusan</label>
                    <input type="text" name="jurusan" placeholder="Contoh: Rekayasa Perangkat Lunak" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="flex justify-end items-center space-x-4 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm">Batal & Kembali</a>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded shadow transition">
                        Simpan Nasabah
                    </button>
                </div>
            </form>
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
