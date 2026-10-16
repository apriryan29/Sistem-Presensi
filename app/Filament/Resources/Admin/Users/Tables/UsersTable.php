<?php

namespace App\Filament\Resources\Admin\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('username')
                    ->label('Nama Pengguna')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Peran Sistem')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('password')
                    ->label('Kata Sandi')
                    ->formatStateUsing(fn () => '••••••••')
                    ->sortable(false)
                    ->searchable(false),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label(''),

                DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengguna')
                    ->modalDescription(
                        'Apakah Anda yakin ingin menghapus pengguna ini?'
                    )
                    ->before(function (DeleteAction $action): void {
                        $record = $action->getRecord();

                        // Cegah penghapusan akun sendiri.
                        if ($record->is(Auth::user())) {
                            Notification::make()
                                ->title('Penghapusan dibatalkan')
                                ->body(
                                    'Anda tidak dapat menghapus akun yang sedang digunakan.'
                                )
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Cegah penghapusan Admin terakhir.
                        if (
                            $record->role === 'admin' &&
                            User::where('role', 'admin')->count() <= 1
                        ) {
                            Notification::make()
                                ->title('Penghapusan dibatalkan')
                                ->body(
                                    'Admin terakhir tidak dapat dihapus. Minimal harus ada satu Admin.'
                                )
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->using(function ($records): void {
                            $jumlahAdmin = User::where('role', 'admin')->count();
                            $jumlahAdminDihapus = 0;
                            $jumlahDihapus = 0;
                            $jumlahDilewati = 0;

                            foreach ($records as $record) {
                                // Cegah penghapusan akun sendiri.
                                if ($record->is(Auth::user())) {
                                    $jumlahDilewati++;
                                    continue;
                                }

                                // Jangan hapus Admin terakhir.
                                if (
                                    $record->role === 'admin' &&
                                    ($jumlahAdmin - $jumlahAdminDihapus) <= 1
                                ) {
                                    $jumlahDilewati++;
                                    continue;
                                }

                                $record->delete();
                                $jumlahDihapus++;

                                if ($record->role === 'admin') {
                                    $jumlahAdminDihapus++;
                                }
                            }

                            Notification::make()
                                ->title('Proses hapus pengguna selesai')
                                ->body(
                                    "Berhasil dihapus: {$jumlahDihapus}. Pengguna dilewati: {$jumlahDilewati}."
                                )
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}