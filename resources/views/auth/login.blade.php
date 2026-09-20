<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Teller Bank Mini</title>
    <!-- Memanggil Tailwind CSS secara instan -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 border border-slate-200">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-blue-600">E-Teller</h2>
            <p class="text-slate-500 text-sm mt-1">Sistem Informasi Bank Mini Sekolah</p>
        </div>

        <!-- Area Notifikasi (Error/Success) -->
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p class="font-bold">Gagal Masuk</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Form Login -->
        <form action="/login" method="POST">
            @csrf <!-- Wajib ada di Laravel untuk keamanan dari serangan CSRF -->

            <div class="mb-5">
                <label for="username" class="block text-slate-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" id="username" class="shadow-sm appearance-none border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required placeholder="Masukkan username admin / pegawai" autocomplete="off">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-slate-700 text-sm font-bold mb-2">Password / PIN</label>
                <input type="password" name="password" id="password" class="shadow-sm appearance-none border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300">
                    Masuk ke Sistem
                </button>
            </div>
        </form>

        <p class="text-center text-slate-400 text-xs mt-8 font-medium">
            &copy; {{ date('Y') }} Jurusan Rekayasa Perangkat Lunak.
        </p>
    </div>

</body>
</html>
