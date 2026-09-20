<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR DENGAN PROFIL DROPDOWN -->
    <nav class="bg-emerald-600 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">E-Teller | Loket Transaksi</div>
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/teller/dashboard') }}" class="text-emerald-100 hover:text-white transition font-medium">Dashboard</a>
                    <a href="{{ url('/teller/riwayat') }}" class="text-white font-bold underline decoration-2 underline-offset-4">Riwayat Saya</a>

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
        <div class="bg-emerald-100 border-l-4 border-emerald-600 text-emerald-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-100 border-l-4 border-rose-600 text-rose-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('error') }}
        </div>
        @endif

        <div class="mb-8 border-b border-slate-200 pb-4">
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Transaksi Saya</h1>
            <p class="text-slate-600 mt-1">Gunakan tombol <strong>Koreksi/Void</strong> jika terjadi kesalahan input. Wajib menggunakan PIN Supervisor.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="min-w-full bg-white text-left">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="py-4 px-6 font-bold">Waktu</th>
                        <th class="py-4 px-6 font-bold">Nasabah (NIS)</th>
                        <th class="py-4 px-6 font-bold">Jenis</th>
                        <th class="py-4 px-6 font-bold">Nominal</th>
                        <th class="py-4 px-6 font-bold">Status</th>
                        <th class="py-4 px-6 font-bold text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    @forelse ($riwayat as $r)
                    <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                        <td class="py-4 px-6 text-slate-500">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-4 px-6 font-bold text-slate-800">{{ $r->nama_siswa }} <br><span class="text-xs text-slate-500 font-mono">{{ $r->nis }}</span></td>
                        <td class="py-4 px-6 uppercase font-bold text-xs">{{ $r->jenis_transaksi }}</td>
                        <td class="py-4 px-6 font-black text-emerald-600">Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
                        <td class="py-4 px-6">
                            @if($r->status == 'dibatalkan')
                                <span class="bg-rose-100 text-rose-700 font-bold px-2 py-1 rounded text-xs uppercase">DIBATALKAN</span>
                                <div class="text-xs text-rose-500 mt-1">Alasan: {{ $r->alasan_batal }}</div>
                            @else
                                <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded text-xs uppercase">{{ $r->status }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($r->status != 'dibatalkan')
                                <button onclick="bukaModalVoid({{ $r->id }}, '{{ $r->nama_siswa }}', '{{ $r->jenis_transaksi }}', 'Rp {{ number_format($r->nominal, 0, ',', '.') }}')" class="bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold py-1.5 px-4 rounded text-xs transition shadow-sm border border-amber-300">
                                    Void / Koreksi
                                </button>
                            @else
                                <span class="text-xs text-slate-400 italic">Telah Di-Void</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-500">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <!-- POP-UP MODAL VOID -->
    <div id="modal-void" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 flex items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-rose-600 px-6 py-4 border-b border-rose-700">
                <h3 class="text-xl font-black text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    KOREKSI / VOID TRANSAKSI
                </h3>
            </div>

            <form id="form-void" method="POST" class="p-6">
                @csrf
                <div class="mb-4 bg-rose-50 p-3 rounded border border-rose-200 text-sm">
                    <p class="text-rose-800 font-bold mb-1">Membatalkan Transaksi:</p>
                    <p id="void-info" class="text-rose-600 font-medium"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Alasan Pembatalan</label>
                    <input type="text" name="alasan_batal" placeholder="Contoh: Salah input nominal uang..." class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-rose-500 focus:outline-none text-sm" required autocomplete="off">
                </div>

                <div class="mb-6">
                    <label class="block text-slate-700 text-sm font-bold mb-2">PIN Otorisasi Supervisor <span class="text-xs text-rose-500 font-normal">(Wajib diisi oleh Atasan)</span></label>
                    <input type="password" name="pin_supervisor" placeholder="Masukkan 6 Digit PIN" class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-rose-500 focus:outline-none text-center tracking-widest text-lg font-bold" required>
                    <p class="text-xs text-slate-400 mt-1">*Sebagai ujicoba, masukkan PIN: 123456</p>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="tutupModalVoid()" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-2 rounded transition">Batal</button>
                    <button type="submit" onclick="return confirm('Tindakan ini tidak bisa dikembalikan. Yakin Void?')" class="w-2/3 bg-rose-600 hover:bg-rose-700 text-white font-black py-2 rounded shadow transition">
                        PROSES VOID
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        function bukaModalVoid(id, nama, jenis, nominal) {
            document.getElementById('form-void').action = "{{ url('/teller/transaksi/void') }}/" + id;
            document.getElementById('void-info').innerText = nama + " | " + jenis.toUpperCase() + " | " + nominal;
            document.getElementById('modal-void').classList.remove('hidden');
        }

        function tutupModalVoid() {
            document.getElementById('modal-void').classList.add('hidden');
        }
    </script>
</body>
</html>
