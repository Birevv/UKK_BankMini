<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Nasabah</title>
</head>
<body class="bg-slate-50 p-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow border">
        <h1 class="text-xl font-bold mb-5">Edit Data Nasabah</h1>
        <form action="/admin/edit-nasabah/{{ $nasabah->id }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold">Nama Siswa</label>
                <input type="text" name="nama_siswa" value="{{ $nasabah->nama_siswa }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold">Kelas</label>
                <input type="text" name="kelas" value="{{ $nasabah->kelas }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold">Jurusan</label>
                <input type="text" name="jurusan" value="{{ $nasabah->jurusan }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="flex justify-end gap-2">
                <a href="/admin/data-nasabah" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</a>
                <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>
