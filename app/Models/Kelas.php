<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $fillable = [
        'nama_kelas',
        'wali_kelas_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Kelas $kelas): void {
            $kelas->nama_kelas = trim((string) $kelas->nama_kelas);
        });

        static::deleting(function (Kelas $kelas): void {
            if ($kelas->siswas()->exists()) {
                throw new \Exception('Kelas masih memiliki siswa.');
            }
        });

        static::updating(function (Kelas $kelas): void {
            if (! $kelas->isDirty('nama_kelas')) {
                return;
            }

            $lama = (string) $kelas->getOriginal('nama_kelas');
            $kelas->nama_kelas = trim((string) $kelas->nama_kelas);

            Siswa::query()
                ->where('kelas', $lama)
                ->update(['kelas' => $kelas->nama_kelas]);
        });
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(
            Siswa::class,
            'kelas',
            'nama_kelas'
        );
    }
}