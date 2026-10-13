<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use PhpParser\Node\Stmt\Label;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

     public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent()
                ->Label('Alamat Email'),

            $this->getPasswordFormComponent(),
            
            Select::make('role')
                ->label('Masuk sebagai')
                ->options([
                    'admin' => 'Admin/Kepala Sekolah',
                    'guru' => 'Guru',
                ])
                ->placeholder('Pilih level pengguna')
                ->required()
                ->native(false),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ];
    }
}
