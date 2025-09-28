<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::twoFactorChallengeView(fn (): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn (): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory => view('livewire.auth.confirm-password'));

        RateLimiter::for('two-factor', fn(Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
