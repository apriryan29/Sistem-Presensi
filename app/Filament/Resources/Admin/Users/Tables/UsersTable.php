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

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
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
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(''),

                DeleteAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengguna')
                    ->modalDescription(
                        'Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.'
                    )
                    ->before(function (DeleteAction $action): void {
                        $record = $action->getRecord();

                        if (
                            $record->role === 'admin' &&
                            User::where('role', 'admin')->count() <= 1
                        ) {
                            Notification::make()
                                ->title('Penghapusan dibatalkan')
                                ->body(
                                    'Admin terakhir tidak dapat dihapus. Minimal harus ada satu Admin dalam sistem ini.'
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

                            $adminTerpilih = $records
                                ->filter(fn (User $user): bool => $user->role === 'admin')
                                ->count();

                            if ($jumlahAdmin - $adminTerpilih < 1) {
                                Notification::make()
                                    ->title('Penghapusan dibatalkan')
                                    ->body('Minimal harus ada satu Admin dalam Sistem ini!.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            foreach ($records as $record) {
                                if (
                                    $record->role !== 'admin' ||
                                    $jumlahAdmin > 1
                                ) {
                                    $record->delete();

                                    if ($record->role === 'admin') {
                                        $jumlahAdmin--;
                                    }
                                }
                            }
                        }),
                ]),
            ]);
    }
}