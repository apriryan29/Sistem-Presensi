<?php

namespace App\Filament\Pages\Guru;

use App\Models\Siswa;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DataSiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.guru.data-siswa';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Siswa';

    protected static ?string $title = 'Siswa';
    public function getBreadcrumbs(): array
    {
        return [
            url('/admin/siswa') => 'Siswa',
            '#' => 'Data',
        ];
    }
    // Hanya guru yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return Auth::check()
            && Auth::user()->role === 'guru';
    }

    // Mengambil data siswa
    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        return Siswa::query()
            ->whereIn('kelas', function ($query) use ($user) {
                $query->select('nama_kelas')
                    ->from('kelas')
                    ->where('wali_kelas_id', $user->id);
            });
    }

    // Konfigurasi tabel
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->formatStateUsing(
                        fn ($state) => 'Kelas ' . $state
                    )
                    ->sortable()
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            $search = preg_replace(
                                '/^kelas\s*/i',
                                '',
                                trim($search)
                            );

                            return $query->where(
                                'kelas',
                                'like',
                                "%{$search}%"
                            );
                        }
                    ),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50]);
    }
}