<?php

namespace App\Providers;

use App\Helper\ArrayHelper;
use Config;
use Illuminate\Support\ServiceProvider;
use Inerba\DbConfig\DbConfig;
use Log;

class DynamicMailConfigServiceProvider extends ServiceProvider
{
    private array $mailConfig;

    // Mail configuration properties
    private string $mailDriver;
    private ?string $mailScheme = null;
    private string $mailHost;
    private string $mailPort;
    private ?string $mailUsername = null;
    private ?string $mailPassword = null;
    private string $mailFromAddress;
    private string $mailFromName;
    private int $mailTimeout;
    private string $mailEHLO;
    private string $appUrl;



    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadConfiguration();

        Config::set('mail.default', $this->mailDriver ?? config('mail.default'));

        Config::set('mail.mailers.smtp', [
            'transport' => $this->mailDriver,
            'scheme' => $this->mailScheme,
            'host' => $this->mailHost,
            'port' => $this->mailPort,
            'username' => $this->mailUsername,
            'password' => $this->mailPassword,
            'timeout' => $this->mailTimeout,
            'local_domain' => $this->mailEHLO,
        ]);

        Config::set('mail.from', [
            'address' => $this->mailFromAddress,
            'name' => $this->mailFromName,
        ]);

    }

    private function loadConfiguration(): void
    {
        try {
            $group = DbConfig::getGroup('mail') ??  [];
            $this->mailConfig = (in_array($group, [null, []], true)) ? [] : $group;
            $this->mailConfig['app_url'] = db_config('website.site_url') ?? env('APP_URL', 'http://localhost');
        } catch (\Throwable) {
            Log::warning('Database mail configuration unavailable; falling back to env values.');
            $this->mailConfig = ['app_url' => env('APP_URL', 'http://localhost')];
        }

        // Load mail configuration
        $this->appUrl = $this->getConfig('app_url');
        $this->mailDriver = $this->getConfig('mail_driver', 'smtp');
        $this->mailScheme = $this->getConfig('mail_scheme', env('MAIL_SCHEME'));
        $this->mailHost = $this->getConfig('mail_host', env('MAIL_HOST', '127.0.0.1'));
        $this->mailPort = $this->getConfig('mail_port', env('MAIL_PORT', 2525));
        $this->mailUsername = $this->getConfig('mail_username', env('MAIL_USERNAME', 'username'));
        $this->mailPassword = $this->getConfig('mail_password', env('MAIL_PASSWORD', 'password'));
        $this->mailFromName = $this->getConfig('mail_from_name', env('MAIL_FROM_NAME', 'Example'));
        $this->mailFromAddress = $this->getConfig('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $this->mailTimeout = $this->getConfig('mail_timeout', env('MAIL_TIMEOUT', 15));
        $this->mailEHLO = $this->getConfig('mail_ehlo', env('MAIL_EHLO', parse_url($this->appUrl, PHP_URL_HOST)));
    }

    private function getConfig(string $key, $default = null)
    {
        return ArrayHelper::getValueFromArray($key, $this->mailConfig) ?? $default;
    }
}
