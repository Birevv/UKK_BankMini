<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiAvalis extends Model
{
    use HasFactory;

    protected $table = 'absensi_avalis';

    protected $fillable = [
        'tanggal',
        'nama',
        'kelas',
        'no_wa',
        'shift_jaga',
        'status',
        'keterangan',
        'created_by',
    ];
}
