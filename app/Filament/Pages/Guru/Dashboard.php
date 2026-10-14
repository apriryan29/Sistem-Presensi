<?php

namespace App\Filament\Pages\Guru;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.guru.dashboard';

    protected static ?string $title = 'Dashboard Guru';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $slug = 'guru-dashboard';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'guru';
    }
}