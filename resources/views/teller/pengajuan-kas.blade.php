<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Tambahan Kas - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR KONSISTEN DENGAN ADMIN (bg-slate-800) -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>

                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <a href="{{ url('/teller/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                    <a href="{{ url('/teller/riwayat') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Riwayat Saya</a>
                    <a href="{{ url('/teller/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tutup Kas</a>
                    <a href="{{ url('/teller/pengajuan-kas') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                    <a href="{{ url('/teller/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                        <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                        Absensi Avalis
                    </a>

                    <!-- PROFIL DROPDOWN -->
                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'P', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <a href="{{ url('/teller/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
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

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER & STATUS LIKUIDITAS -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Permintaan & Pengajuan Tambahan Kas Loket (Droping Dana)
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Gunakan fitur ini jika uang fisik di laci loket habis atau menipis agar kas tidak pernah bernilai minus saat melayani penarikan nasabah.
                </p>
            </div>

            <!-- TOMBOL ATUR KAS AWAL -->
            <div class="mt-4 md:mt-0">
                <button type="button" onclick="openModalKasAwal()"
                        class="bg-white hover:bg-slate-100 text-slate-700 font-bold px-4 py-2 rounded-lg text-xs border border-slate-300 transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Atur Kas Awal Hari Ini
                </button>
            </div>
        </div>

        <!-- 3 KARTU KONDISI KAS HARI INI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kas Awal Hari Ini</p>
                <p class="text-2xl font-black text-slate-800 mt-1">Rp {{ number_format($kasAwal, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Modal operasional saat loket dibuka</p>
            </div>

            <div class="p-5 rounded-xl border shadow-sm {{ $kasTersedia <= 0 ? 'bg-rose-50 border-rose-300 text-rose-900' : ($kasTersedia < 100000 ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900') }}">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider">Sisa Kas Fisik Loket Saat Ini</p>
                    <span class="text-[10px] font-black px-2 py-0.5 rounded {{ $kasTersedia <= 0 ? 'bg-rose-600 text-white' : ($kasTersedia < 100000 ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white') }}">
                        {{ $kasTersedia <= 0 ? 'KAS HABIS' : ($kasTersedia < 100000 ? 'KAS MENIPIS' : 'KAS AMAN') }}
                    </span>
                </div>
                <p class="text-3xl font-black mt-2">Rp {{ number_format($kasTersedia, 0, ',', '.') }}</p>
                <p class="text-xs mt-1 font-medium">
                    {{ $kasTersedia <= 0 ? 'Uang fisik habis! Penarikan nasabah terkunci sampai pengajuan disetujui.' : 'Batas maksimal penarikan tunai yang bisa dilayani langsung.' }}
                </p>
            </div>

            <div class="bg-blue-900 text-white p-5 rounded-xl shadow-md border border-blue-800">
                <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">Aturan Kas Minus Perbankan</p>
                <p class="text-sm font-bold mt-2">🛡️ Proteksi Angka Minus Aktif</p>
                <p class="text-xs text-blue-200 mt-1 leading-relaxed">
                    Sistem secara ketat menolak penarikan jika melebihi kas fisik. Pengajuan kas harus disetujui Supervisor sebelum transaksi penarikan besar dapat dilanjutkan.
                </p>
            </div>
        </div>

        <!-- FORMULIR PENGAJUAN KAS & RIWAYAT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- FORM INPUT PENGAJUAN KAS -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-slate-800 pb-3 mb-4 border-b border-slate-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Formulir Permintaan Tambahan Kas
                </h2>

                <form action="{{ url('/teller/pengajuan-kas/kirim') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nominal Dana yang Diajukan (Rp) *
                        </label>
                        <input type="number" name="nominal" id="nominal" min="10000" step="5000" required placeholder="Contoh: 500000"
                               class="w-full text-lg font-black border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-900 bg-slate-50 focus:bg-white">
                        <p class="text-[11px] text-slate-400 mt-1">Minimal pengajuan Rp 10.000.</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Alasan Kebutuhan Kas *
                        </label>
                        <textarea name="alasan" rows="3" required placeholder="Jelaskan kebutuhan kas, contoh: Kas laci habis karena banyak siswa menarik tabungan untuk praktikum/kegiatan sekolah..."
                                  class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-black py-3 px-4 rounded-lg shadow transition flex items-center justify-center text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Pengajuan ke Supervisor
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-slate-100 text-[11px] text-slate-500">
                    <p>💡 <em>Setelah disetujui Supervisor, saldo kas di loket kasir Anda otomatis bertambah seketika dan transaksi penarikan dapat langsung dilanjutkan.</em></p>
                </div>
            </div>

            <!-- TABEL RIWAYAT PENGAJUAN KAS SAYA -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-base font-bold text-slate-800">Riwayat Pengajuan Kas Saya</h2>
                    <span class="text-xs text-slate-500">{{ count($pengajuanList) }} Pengajuan Tercatat</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">No. Pengajuan</th>
                                <th class="p-3">Tanggal & Waktu</th>
                                <th class="p-3 text-right">Nominal (Rp)</th>
                                <th class="p-3">Alasan Permintaan</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3">Catatan Supervisor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($pengajuanList as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-blue-900">{{ $p->nomor_pengajuan }}</td>
                                <td class="p-3 font-mono text-slate-600">
                                    {{ date('d/m/Y', strtotime($p->tanggal)) }}<br>
                                    <span class="text-[10px] text-slate-400">{{ date('H:i', strtotime($p->created_at)) }} WIB</span>
                                </td>
                                <td class="p-3 text-right font-black font-mono text-amber-700 text-sm">
                                    Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-slate-700 max-w-xs">
                                    {{ $p->alasan }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($p->status === 'disetujui')
                                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded flex items-center justify-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            DISETUJUI
                                        </span>
                                    @elseif($p->status === 'ditolak')
                                        <span class="bg-rose-100 text-rose-800 text-[10px] font-black px-2 py-0.5 rounded">
                                            DITOLAK
                                        </span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded animate-pulse">
                                            PENDING (MENUNGGU)
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-600 text-[11px]">
                                    @if($p->nama_spv)
                                        <span class="font-bold text-slate-800">{{ $p->nama_spv }}:</span>
                                    @endif
                                    {{ $p->catatan_spv ?? ($p->status === 'pending' ? 'Belum ditinjau' : '-') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    Belum ada pengajuan kas yang dibuat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL ATUR KAS AWAL HARI INI -->
    <div id="modal-kas-awal" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-slate-800 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold">Tetapkan Kas Awal Hari Ini</h3>
                <button type="button" onclick="closeModalKasAwal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ url('/kas-awal/set') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">
                            Nominal Kas Awal Laci Loket (Rp) *
                        </label>
                        <input type="number" name="saldo_awal" required min="0" value="{{ $kasAwal }}"
                               placeholder="Contoh: 1000000"
                               class="w-full text-base font-black border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600 text-slate-900">
                        <p class="text-[11px] text-slate-400 mt-1">Uang tunai modal awal yang disiapkan di laci teller pagi ini.</p>
                    </div>
                </div>

                <div class="bg-slate-100 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModalKasAwal()" class="text-xs font-bold text-slate-700 bg-white hover:bg-slate-200 px-4 py-2 rounded-lg border border-slate-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-black text-white bg-blue-800 hover:bg-blue-900 px-5 py-2 rounded-lg shadow transition">
                        Simpan Kas Awal
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        function openModalKasAwal() { document.getElementById('modal-kas-awal').classList.remove('hidden'); }
        function closeModalKasAwal() { document.getElementById('modal-kas-awal').classList.add('hidden'); }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModalKasAwal();
        });

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
