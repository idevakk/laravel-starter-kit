<?php

namespace App\Providers\Filament;

use App\Helper\ArrayHelper;
use App\Helper\ColorHelper;
use Auth;
use Backstage\FilamentMails\Facades\FilamentMails;
use Backstage\FilamentMails\FilamentMailsPlugin;
use Boquizo\FilamentLogViewer\FilamentLogViewerPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Inerba\DbConfig\DbConfig;
use Jacobtims\FilamentLogger\FilamentLoggerPlugin;

class DashPanelProvider extends PanelProvider
{
    private string $panelId;
    private array $panelConfig;

    // Panel configuration properties
    private bool $panelDefault;
    private string $panelPath;
    private string $panelBrandName;
    private string $panelFont;
    private array $panelColor;
    private bool $panelLogin;
    private bool $panelRegistration;
    private bool $panelProfile;
    private bool $panelSPA;
    private bool $panelSPAPrefetch;
    private array $panelSPAExceptions;
    private bool $panelBrandLogoShow;
    private string $panelBrandLogoLight;
    private string $panelBrandLogoDark;
    private ?string $panelBrandLogoHeight = null;
    private string $panelFavicon;

    // MFA configuration properties
    private bool $panelMFA;
    private string $panelMFAType;
    private string $panelMFABrandName;
    private bool $panelMFARecoverable;
    private bool $panelMFARecoveryCodeRegenerate;
    private int $panelMFACodeWindow;
    private int $panelMFARecoveryCodeCount;
    private int $panelMFAEmailCodeExpiry;
    private bool $panelMFARequired;

    /**
     * Configure the Filament panel.
     */
    public function panel(Panel $panel): Panel
    {
        $this->panelId = 'dash';
        $this->loadConfiguration();

        $panel = $panel
            ->default($this->panelDefault)
            ->id($this->panelId)
            ->path($this->panelPath)
            ->colors($this->panelColor)
            ->brandName($this->panelBrandName)
            ->font($this->panelFont)
            ->favicon(asset($this->panelFavicon))
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
                FilamentMailsPlugin::make()->canManageMails(function (): bool {
                    $user = Auth::user();
                    if ($user->hasRole('admin')) {
                        return true;
                    }
                    return (bool) $user->hasPermissionTo('manage mails');
                }),
            ])
            ->routes(fn() => FilamentMails::routes());

        $this->applyConditionalConfiguration($panel);

        return $panel;
    }

    private function loadConfiguration(): void
    {
        try {
            $group = DbConfig::getGroup('panel');
            $this->panelConfig = (in_array($group, [null, []], true)) ? [] : $group;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        // Load basic panel configuration
        $this->panelDefault = $this->getConfig('panel_default') === $this->panelId;
        $this->panelPath = $this->getConfig("panel_route_{$this->panelId}", $this->panelId);
        $this->panelBrandName = $this->getConfig("panel_name_{$this->panelId}", config('app.name'));
        $this->panelFont = $this->getConfig("panel_font_{$this->panelId}", 'Poppins');
        $this->panelColor = ColorHelper::getPanelColor($this->panelId);

        // Load feature flags
        $this->panelLogin = $this->getConfig("panel_enable_login_{$this->panelId}", true);
        $this->panelRegistration = $this->getConfig("panel_enable_registration_{$this->panelId}", false);
        $this->panelProfile = $this->getConfig("panel_enable_profile_{$this->panelId}", false);

        // Load SPA configuration
        $this->panelSPA = $this->getConfig("panel_spa_{$this->panelId}", false);
        $this->panelSPAPrefetch = $this->getConfig("panel_spa_prefetch_{$this->panelId}", false);
        $this->panelSPAExceptions = $this->getConfig("panel_spa_exceptions_{$this->panelId}", []);

        // Load branding configuration
        $this->panelBrandLogoShow = $this->getConfig("panel_show_logo_{$this->panelId}") === 'show';
        $this->panelBrandLogoLight = $this->getConfig("panel_logo_light_{$this->panelId}", 'favicon.svg');
        $this->panelBrandLogoDark = $this->getConfig("panel_logo_dark_{$this->panelId}", 'favicon.svg');
        $this->panelBrandLogoHeight = $this->getConfig("panel_logo_height_{$this->panelId}");
        $this->panelFavicon = $this->getConfig("panel_favicon_{$this->panelId}", 'favicon.svg');

        // Load MFA configuration
        $this->panelMFA = $this->getConfig("panel_enable_mfa_{$this->panelId}", false);
        $this->panelMFAType = $this->getConfig("panel_mfa_type_{$this->panelId}", 'app');
        $this->panelMFABrandName = $this->getConfig("panel_mfa_app_brand_name_{$this->panelId}", config('app.name'));
        $this->panelMFARecoverable = $this->getConfig("panel_mfa_app_recoverable_{$this->panelId}", false);
        $this->panelMFARecoveryCodeRegenerate = $this->getConfig("panel_mfa_app_recovery_code_regeneratable_{$this->panelId}", false);
        $this->panelMFACodeWindow = $this->getConfig("panel_mfa_app_code_window_{$this->panelId}", 4);
        $this->panelMFARecoveryCodeCount = $this->getConfig("panel_mfa_app_recovery_code_count_{$this->panelId}", 8);
        $this->panelMFAEmailCodeExpiry = $this->getConfig("panel_mfa_email_code_expiry_{$this->panelId}", 2);
        $this->panelMFARequired = $this->getConfig("panel_mfa_is_required_{$this->panelId}") === 'yes';
    }

    private function applyConditionalConfiguration(Panel $panel): void
    {
        $actions = [
            'login' => [$this->panelLogin, fn(): \Filament\Panel => $panel->login()],
            'register' => [$this->panelRegistration, fn(): \Filament\Panel => $panel->registration()],
            'profile' => [$this->panelProfile, fn(): \Filament\Panel => $panel->profile()],
            'spa' => [$this->panelSPA, fn(): \Filament\Panel => $panel
                ->spa(hasPrefetching: $this->panelSPAPrefetch)
                ->spaUrlExceptions(exceptions: $this->panelSPAExceptions)
            ],
            'logo' => [$this->panelBrandLogoShow, fn(): \Filament\Panel => $panel
                ->brandLogo(asset($this->panelBrandLogoLight))
                ->darkModeBrandLogo(asset($this->panelBrandLogoDark))
                ->brandLogoHeight($this->panelBrandLogoHeight)
            ],
        ];

        foreach ($actions as [$condition, $callback]) {
            if ($condition) {
                $callback();
            }
        }

        $this->configureMFA($panel);
    }

    private function getConfig(string $key, $default = null)
    {
        return ArrayHelper::getValueFromArray($key, $this->panelConfig) ?? $default;
    }

    private function configureMFA(Panel $panel): void
    {
        if (!$this->panelMFA) {
            return;
        }

        $authentication = $this->panelMFAType === 'app'
            ? AppAuthentication::make()
                ->brandName($this->panelMFABrandName)
                ->recoverable($this->panelMFARecoverable)
                ->recoveryCodeCount($this->panelMFARecoveryCodeCount)
                ->regenerableRecoveryCodes($this->panelMFARecoveryCodeRegenerate)
                ->codeWindow($this->panelMFACodeWindow)
            : EmailAuthentication::make()
                ->codeExpiryMinutes($this->panelMFAEmailCodeExpiry);

        $panel->multiFactorAuthentication([$authentication], isRequired: $this->panelMFARequired);
    }
}
