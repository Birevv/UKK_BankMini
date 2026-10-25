<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Nasabah - LKM Mitra Siswa Abadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR NASABAH KONSISTEN DENGAN TEMA SISTEM -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>

                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <a href="{{ url('/nasabah/dashboard') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Dashboard</a>
                    @if($nasabah)
                        <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Buku Tabungan Digital</a>
                    @endif

                    <!-- PROFIL DROPDOWN -->
                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($nasabah->nama_siswa ?? Auth::user()->nama_petugas ?? 'N', 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ $nasabah->nama_siswa ?? Auth::user()->nama_petugas ?? 'Nasabah' }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $nasabah->nama_siswa ?? Auth::user()->nama_petugas ?? 'Nasabah' }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">
                                    NASABAH / SISWA
                                </span>
                            </div>

                            @if($nasabah)
                                <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    Lihat Buku Tabungan
                                </a>
                            @endif

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

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @if(!$nasabah)
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 rounded shadow-sm">
                <p class="font-bold">Data Nasabah Belum Terhubung</p>
                <p class="text-sm">Akun pengguna ini belum memiliki data profil rekening nasabah.</p>
            </div>
        @else
            <!-- KARTU SAMBUTAN & SALDO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- INFO REKENING -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden md:col-span-2 flex flex-col justify-between">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-slate-700 rounded-full opacity-20 pointer-events-none"></div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs uppercase tracking-widest text-slate-400 font-bold">Kartu Rekening Bank Mini</span>
                            <span class="bg-emerald-500/20 text-emerald-300 text-xs px-2.5 py-0.5 rounded-full font-bold uppercase">{{ $nasabah->status ?? 'Aktif' }}</span>
                        </div>
                        <p class="text-2xl font-bold tracking-tight mb-1">{{ $nasabah->nama_siswa }}</p>
                        <p class="text-sm text-slate-400 font-mono">No. Rek: <span class="text-white font-bold">{{ $nasabah->no_rekening }}</span> | NIS: <span class="text-white">{{ $nasabah->nis }}</span></p>
                        <p class="text-xs text-slate-400 mt-1">{{ $nasabah->kelas }} - {{ $nasabah->jurusan }}</p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-700/60 flex items-end justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Saldo Tabungan Saat Ini</p>
                            <p class="text-3xl font-black text-emerald-400 mt-0.5">Rp {{ number_format($nasabah->saldo ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition shadow flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Buka Buku Tabungan
                        </a>
                    </div>
                </div>

                <!-- INFO PINTASAN & STATISTIK -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-3">Informasi Nasabah</h2>
                        <dl class="space-y-2.5 text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Kategori</dt>
                                <dd class="font-bold text-slate-800 uppercase">{{ $nasabah->kategori ?? 'Siswa' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Kelas</dt>
                                <dd class="font-bold text-slate-800">{{ $nasabah->kelas }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Jurusan</dt>
                                <dd class="font-bold text-slate-800">{{ $nasabah->jurusan }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Total Transaksi</dt>
                                <dd class="font-bold text-slate-800">{{ count($transaksi) }} kali</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <a href="{{ url('/buku-tabungan/cetak/'.$nasabah->id) }}" target="_blank" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 rounded-lg text-center block transition">
                            Cetak Cetak Lembar Buku
                        </a>
                    </div>
                </div>
            </div>

            <!-- TABEL 10 TRANSAKSI TERAKHIR -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">10 Mutasi Transaksi Terakhir</h3>
                    <span class="text-xs text-slate-400">Diperbarui secara real-time</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Sandi</th>
                                <th class="px-6 py-3 text-right">Nominal</th>
                                <th class="px-6 py-3 text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($transaksi as $t)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-3 text-xs text-slate-500">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        @if(strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran')
                                            <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-0.5 rounded-full font-bold">Setoran</span>
                                        @else
                                            <span class="bg-rose-100 text-rose-800 text-xs px-2.5 py-0.5 rounded-full font-bold">Penarikan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 font-mono text-xs">{{ $t->kode_sandi ?? '-' }}</td>
                                    <td class="px-6 py-3 text-right font-bold {{ (strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran') ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ (strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran') ? '+' : '-' }} Rp {{ number_format($t->nominal ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-black text-slate-800">
                                        Rp {{ number_format($t->saldo_akhir ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">Belum ada riwayat transaksi mutasi buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>

    <script>
        function toggleDropdown() {
            const d = document.getElementById('profile-dropdown');
            if (d) d.classList.toggle('hidden');
        }
        window.addEventListener('click', function(e) {
            const btn = document.getElementById('profile-btn');
            const d = document.getElementById('profile-dropdown');
            if (btn && d && !btn.contains(e.target) && !d.contains(e.target)) {
                d.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
