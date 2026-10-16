<?php

namespace App\Filament\Resources\Admin\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('username')
                    ->label('Nama Pengguna')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255),

                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->required()
                    ->maxLength(255),

                Select::make('role')
                    ->label('Peran Sistem')
                    ->options([
                        'admin' => 'Admin/Kepala Sekolah',
                        'guru' => 'Guru',
                    ])
                    ->required()
                    ->rules([
                        function (?User $record) {
                            return function (
                                string $attribute,
                                $value,
                                $fail
                            ) use ($record) {
                                if (
                                    $record?->role !== 'admin' ||
                                    $value !== 'guru'
                                ) {
                                    return;
                                }

                                // Admin tidak boleh mengubah role akunnya sendiri.
                                if ($record->is(Auth::user())) {
                                    $fail(
                                        'Anda tidak dapat mengubah akun anda menjadi Guru.'
                                    );

                                    return;
                                }

                                // Jangan sampai tidak ada Admin yang tersisa.
                                if (
                                    User::where('role', 'admin')->count() <= 1
                                ) {
                                    $fail(
                                        'Role tidak dapat diubah karena hanya tersisa satu akun Admin.'
                                    );
                                }
                            };
                        },
                    ]),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->required(
                        fn (string $operation): bool => $operation === 'create'
                    )
                    ->maxLength(255),
            ]);
    }
}