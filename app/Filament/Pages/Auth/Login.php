<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';
    public ?string $loginError = null;

    public int $loginErrorCount = 0;
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


    public function authenticate(): ?LoginResponse
    {
        $this->loginError = null;

        try {
            return parent::authenticate();
        } catch (ValidationException $e) {
            $errors = $e->errors();

            $this->showError(
                array_key_exists('data.email', $errors)
                    ? 'Email, password, atau level pengguna salah.'
                    : 'Lengkapi semua isian terlebih dahulu.'
            );

            return null;
        }
    }

    // Batas percobaan login: tampilkan di banner, bukan toast.
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        $this->showError("Terlalu banyak percobaan login. Coba lagi dalam {$exception->secondsUntilAvailable} detik.");

        return null;
    }

    private function showError(string $message): void
    {
        $this->loginError = $message;
        $this->loginErrorCount++;
    }
}
