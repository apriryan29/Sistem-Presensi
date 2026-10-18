<?php

namespace App\Filament\Pages\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class LaporanKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.admin.laporan-kelas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan Presensi Kelas';

    public ?string $tanggalAwal = null;

    public ?string $tanggalAkhir = null;

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/laporan-kelas') => 'Laporan',
            '#' => 'Data',
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public function resetTanggal(): void
    {
        $this->tanggalAwal = null;
        $this->tanggalAkhir = null;
    }

    /** Periode valid: kosong semua, atau terisi keduanya dengan akhir >= awal. */
    public function periodeValid(): bool
    {
        if (blank($this->tanggalAwal) && blank($this->tanggalAkhir)) {
            return true;
        }

        if (blank($this->tanggalAwal) || blank($this->tanggalAkhir)) {
            return false;
        }

        return $this->tanggalAkhir >= $this->tanggalAwal;
    }

    public function table(Table $table): Table
    {
        $kelasTable = (new Kelas())->getTable();
        $siswaTable = (new Siswa())->getTable();

        return $table
            ->query(
                Kelas::query()
                    ->select($kelasTable . '.*')
                    ->selectSub(
                        Siswa::query()
                            ->selectRaw('COUNT(*)')
                            ->whereColumn($siswaTable . '.kelas', $kelasTable . '.nama_kelas'),
                        'siswas_count'
                    )
                    ->orderByRaw($kelasTable . ".nama_kelas NOT REGEXP '^[0-9]+$'")
                    ->orderByRaw('CAST(' . $kelasTable . '.nama_kelas AS UNSIGNED)')
                    ->orderBy($kelasTable . '.nama_kelas')
            )
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->formatStateUsing(
                        fn ($state): string => ctype_digit(trim((string) $state))
                            ? 'Kelas ' . $state
                            : (string) $state
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('waliKelas.name')
                    ->label('Wali Kelas')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('siswas_count')
                    ->label('Jumlah Siswa')
                    ->counts('siswas')
                    ->formatStateUsing(fn ($state): string => $state . ' Siswa')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('info')
                    ->tooltip(
                        fn (): string => $this->periodeValid()
                            ? 'Lihat PDF rekap presensi tanpa mengunduh'
                            : 'Isi tanggal mulai dan selesai dengan benar dulu'
                    )
                    ->disabled(fn (): bool => ! $this->periodeValid())
                    ->url(fn (Kelas $record): string => route(
                        'admin.laporan-kelas.pdf',
                        array_filter([
                            'kelas'         => $record->getKey(),
                            'tanggal_awal'  => $this->tanggalAwal,
                            'tanggal_akhir' => $this->tanggalAkhir,
                            'aksi'          => 'lihat',
                        ])
                    ))
                    ->openUrlInNewTab(),

                Action::make('cetak')
                    ->label('Unduh')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('warning')
                    ->tooltip(
                        fn (): string => $this->periodeValid()
                            ? 'Unduh PDF rekap presensi siswa kelas ini'
                            : 'Isi tanggal mulai dan selesai dengan benar dulu'
                    )
                    ->disabled(fn (): bool => ! $this->periodeValid())
                    ->url(fn (Kelas $record): string => route(
                        'admin.laporan-kelas.pdf',
                        array_filter([
                            'kelas'         => $record->getKey(),
                            'tanggal_awal'  => $this->tanggalAwal,
                            'tanggal_akhir' => $this->tanggalAkhir,
                        ])
                    ))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}