<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $fillable = [
        'nama',
        'nis',
        'kelas',
        'jenis_kelamin',
    ];

    public function presensis(): HasMany
    {
        return $this->hasMany(\App\Models\Presensi::class);
    }
}