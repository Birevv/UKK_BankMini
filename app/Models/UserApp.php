<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApp extends Model
{
    // Mengarahkan model ini secara spesifik ke tabel user_app (Paket 1)
    protected $table = 'user_app';

    // Matikan timestamps karena tabel di soal tidak meminta kolom created_at/updated_at
    public $timestamps = false;

    protected $guarded = ['id'];

    // Relasi: Akun mobile ini MILIK nasabah siapa?
    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'id_nasabah');
    }
}
