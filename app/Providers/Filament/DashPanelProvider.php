<?php

namespace App\Providers\Filament;

use Auth;
use Backstage\FilamentMails\Facades\FilamentMails;
use Backstage\FilamentMails\FilamentMailsPlugin;
use Boquizo\FilamentLogViewer\FilamentLogViewerPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jacobtims\FilamentLogger\FilamentLoggerPlugin;

class DashPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel =  $panel
            ->default()
            ->id('dash')
            ->path(config('filament-php.route'))
            ->colors(config('filament-php.colors'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentLoggerPlugin::make(),
                FilamentLogViewerPlugin::make(),
                FilamentMailsPlugin::make()->canManageMails(function () {
                    $user = Auth::user();

                    // Allow access for users with specific roles
                    if ($user->hasRole('admin')) {
                        return true;
                    }

                    // Allow access for users with specific permissions
                    if ($user->hasPermissionTo('manage mails')) {
                        return true;
                    }

                    // Restrict access for all other users
                    return false;
                }),
            ])
            ->routes(fn () => FilamentMails::routes());

        if (config('filament-php.enable_login')) {
            $panel->login();
        }

        if (config('filament-php.enable_registration')) {
            $panel->register();
        }

        if (config('filament-php.enable_profile')) {
            $panel->profile();
        }

        return $panel;
    }
}
