<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Pages\Admin\Dashboard as AdminDashboard;
use App\Filament\Pages\Guru\Dashboard as GuruDashboard;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public ?string $loginError = null;

    public int $loginErrorCount = 0;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent()
                ->label('Alamat Email'),

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
        $role = $this->data['role'] ?? null;

        $url = match ($role) {
            'admin' => AdminDashboard::getUrl(panel: 'admin'),
            'guru' => GuruDashboard::getUrl(panel: 'admin'),
            default => null,
        };

        if ($url !== null) {
            redirect()->setIntendedUrl($url);
        }

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

    protected function getRedirectUrl(): string
    {
        $user = Auth::user();

        if ($user?->role === 'guru') {
            return GuruDashboard::getUrl(panel: 'admin');
        }

        return AdminDashboard::getUrl(panel: 'admin');
    }

    protected function getRateLimitedNotification(
        TooManyRequestsException $exception
    ): ?Notification {
        $this->showError(
            "Terlalu banyak percobaan login. Coba lagi dalam {$exception->secondsUntilAvailable} detik."
        );

        return null;
    }

    private function showError(string $message): void
    {
        $this->loginError = $message;
        $this->loginErrorCount++;
    }
}