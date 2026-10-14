<?php

namespace App\Filament\Resources\Admin\Users\Schemas;

use Dom\Text;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
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
                        'guru' => 'Guru',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                ]);
    }
}
