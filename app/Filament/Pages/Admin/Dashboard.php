<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.admin.dashboard';

    protected static ?string $title = 'Dashboard Admin';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $slug = 'admin-dashboard';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}