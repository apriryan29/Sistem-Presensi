<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\Actions\Action;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->favicon(asset('images/logo.png'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render(<<<'BLADE'
                    <div style="padding: 12px 16px; display: flex; justify-content: center;">
                        <button
                            type="button"
                            onclick="document.getElementById('logout-confirm-modal').showModal()"
                            style="
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                width: 100%;
                                padding: 10px 16px;
                                border: none;
                                border-radius: 8px;
                                background: grey;
                                color: white;
                                cursor: pointer;
                            "
                        >
                            <span>Keluar</span>
                        </button>
                    </div>

                    <dialog
                        id="logout-confirm-modal"
                        style="
                            width: min(400px, calc(100% - 32px));
                            padding: 0;
                            border: none;
                            border-radius: 12px;
                            color: inherit;
                            background: white;
                            box-shadow: 0 20px 50px rgba(0,0,0,.25);
                        "
                    >
                        <div style="padding: 24px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <h2 style="font-size: 18px; font-weight: 600; margin: 0;">
                                    Konfirmasi Keluar
                                </h2>
                            </div>

                            <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px;">
                                Apakah Anda yakin ingin keluar dari sistem presensi?
                            </p>

                            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                <button
                                    type="button"
                                    onclick="document.getElementById('logout-confirm-modal').close()"
                                    style="
                                        padding: 9px 16px;
                                        border: 1px solid #d1d5db;
                                        border-radius: 8px;
                                        background: white;
                                        color: #374151;
                                        cursor: pointer;
                                    "
                                >
                                    Batal
                                </button>

                                <form
                                    id="logout-form"
                                    action="{{ filament()->getLogoutUrl() }}"
                                    method="post"
                                    style="margin: 0;"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        style="
                                            padding: 9px 16px;
                                            border: none;
                                            border-radius: 8px;
                                            background: #dc2626;
                                            color: white;
                                            cursor: pointer;
                                        "
                                    >
                                        Ya, Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </dialog>

                    <style>
                        #logout-confirm-modal::backdrop {
                            background: rgba(0, 0, 0, .55);
                            backdrop-filter: blur(2px);
                        }

                        #logout-confirm-modal[open] {
                            position: fixed;
                            inset: 0;
                            margin: auto;
                        }
                    </style>
                BLADE)
            )
            ->userMenuItems([
                'logout' => fn (Action $action): Action => $action
                    ->label('Keluar')
                    ->icon('heroicon-m-arrow-left-on-rectangle')
                    ->color('danger')
                    ->url('#')
                    ->extraAttributes([
                        'onclick' => "event.preventDefault(); document.getElementById('logout-confirm-modal').showModal();",
                    ]),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}