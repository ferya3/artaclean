<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\ScopeToDealer;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * "سیستم نمایندگی" — each dealer signs in here and sees only the leads,
 * quotes and orders routed to them. Scoping is enforced by a global scope
 * applied in middleware, not by hiding buttons.
 */
class DealerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dealer')
            ->path('dealer')
            ->login()
            ->brandName(config('app.name').' — '.__('admin.panel.dealer'))
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
            ])
            ->font('Vazirmatn')
            ->discoverResources(in: app_path('Filament/Dealer/Resources'), for: 'App\Filament\Dealer\Resources')
            ->discoverPages(in: app_path('Filament/Dealer/Pages'), for: 'App\Filament\Dealer\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Dealer/Widgets'), for: 'App\Filament\Dealer\Widgets')
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
                ScopeToDealer::class,
            ]);
    }
}
