<?php

namespace App\Filament\Pages\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DetailKelas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.admin.detail-kelas';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBuildingOffice2;

    protected static bool $shouldRegisterNavigation = false;
    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
    protected static ?string $slug = 'detail-kelas/{record}';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->color('gray')
                ->url(ManajemenKelas::getUrl()),
        ];
    }

    public Kelas $kelas;

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/manajemen-kelas') => 'Kelas',
            '#' => 'Detail',
        ];
    }

    public function mount(int|string $record): void
    {
        $this->kelas = Kelas::with('waliKelas')
            ->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Detail Kelas ' . $this->kelas->nama_kelas;
    }

    protected function nomorKelas(): int
    {
        return (int) $this->kelas->nama_kelas;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Siswa::query()
                    ->where('kelas', $this->nomorKelas())
            )
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->searchable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->formatStateUsing(
                        fn ($state): string => 'Kelas ' . $state
                    ),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('naikKelas')
                        ->label('Naik Kelas')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Naik Kelas')
                        ->modalDescription(
                            'Siswa yang dipilih akan dipindahkan ke kelas berikutnya.'
                        )
                        ->modalSubmitActionLabel('Ya, Naik Kelas')
                        ->modalCancelActionLabel('Batal')
                        ->action(function (Collection $records): void {
                            $nomorKelas = $this->nomorKelas();

                            if ($nomorKelas < 1 || $nomorKelas >= 6) {
                                Notification::make()
                                    ->title('Tidak bisa naik kelas')
                                    ->body(
                                        'Kelas 6 merupakan kelas terakhir.'
                                    )
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $nomorBerikutnya = $nomorKelas + 1;

                            $kelasBerikutnya = Kelas::query()
                                ->where(
                                    'nama_kelas',
                                    (string) $nomorBerikutnya
                                )
                                ->exists();

                            if (! $kelasBerikutnya) {
                                Notification::make()
                                    ->title('Kelas berikutnya tidak tersedia')
                                    ->body(
                                        'Buat kelas ' .
                                        $nomorBerikutnya .
                                        ' terlebih dahulu.'
                                    )
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $jumlahSiswa = DB::transaction(
                                function () use (
                                    $records,
                                    $nomorKelas,
                                    $nomorBerikutnya
                                ): int {
                                    return Siswa::query()
                                        ->whereIn(
                                            'id',
                                            $records->modelKeys()
                                        )
                                        ->where('kelas', $nomorKelas)
                                        ->update([
                                            'kelas' => $nomorBerikutnya,
                                        ]);
                                }
                            );

                            if ($jumlahSiswa === 0) {
                                Notification::make()
                                    ->title('Tidak ada siswa yang dipindahkan')
                                    ->body(
                                        'Periksa kembali siswa yang dipilih.'
                                    )
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Berhasil naik kelas')
                                ->body(
                                    $jumlahSiswa .
                                    ' siswa dipindahkan ke Kelas ' .
                                    $nomorBerikutnya .
                                    '.'
                                )
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}