<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('status', [
                'hadir',
                'izin',
                'sakit',
                'alpa',
            ])->default('hadir');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique(
                ['siswa_id', 'tanggal'],
                'presensis_siswa_tanggal_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};