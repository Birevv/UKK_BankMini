<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pegawai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <nav class="bg-blue-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider">Administrator</div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium">{{ Auth::user()->nama_petugas }}</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-800">Edit Akun Pegawai</h1>
                <p class="text-slate-600 text-sm mt-1">Perbarui data atau ubah password pegawai di bawah ini.</p>
            </div>

            <form action="/admin/edit-pegawai/{{ $pegawai->id }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Username Akun (Tidak bisa diubah)</label>
                    <input type="text" value="{{ $pegawai->username }}" class="bg-slate-100 shadow-sm appearance-none border border-slate-300 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" readonly>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="nama_petugas">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_petugas" id="nama_petugas" value="{{ $pegawai->nama_petugas }}" class="shadow-sm appearance-none border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="shadow-sm appearance-none border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <p class="text-xs text-orange-500 mt-1">*Hanya diisi jika pegawai lupa password.</p>
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="role">Jabatan (Role)</label>
                    <select name="role" id="role" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="teller" {{ $pegawai->role == 'teller' ? 'selected' : '' }}>Petugas Loket (Teller)</option>
                        <option value="supervisor" {{ $pegawai->role == 'supervisor' ? 'selected' : '' }}>Kepala/Supervisor</option>
                        <option value="admin" {{ $pegawai->role == 'admin' ? 'selected' : '' }}>Administrator (Admin)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="/admin/dashboard" class="text-slate-500 hover:text-slate-700 font-medium text-sm transition">Batal & Kembali</a>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-6 rounded-lg focus:outline-none focus:ring-4 focus:ring-amber-300 transition">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
