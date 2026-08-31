<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user can get initials', function (): void {
    $user = new User(['name' => 'John Doe']);
    expect($user->initials())->toBe('JD');

    $user2 = new User(['name' => 'Jane']);
    expect($user2->initials())->toBe('J');
});

test('user can check if admin', function (): void {
    $user = new User(['role' => Role::Admin]);
    expect($user->isAdmin())->toBeTrue()
        ->and($user->hasRole(Role::Admin))->toBeTrue()
        ->and($user->hasRole(Role::User))->toBeFalse();

    $user2 = new User(['role' => Role::User]);
    expect($user2->isAdmin())->toBeFalse()
        ->and($user2->hasRole(Role::User))->toBeTrue()
        ->and($user2->hasRole(Role::Admin))->toBeFalse();
});

test('user can access filament panel only if admin', function (): void {
    $admin = new User(['role' => Role::Admin]);
    $user = new User(['role' => Role::User]);

    $panel = new Panel;

    expect($admin->canAccessPanel($panel))->toBeTrue()
        ->and($user->canAccessPanel($panel))->toBeFalse();
});

test('user can manage app authentication secret', function (): void {
    $user = User::factory()->create();

    expect($user->getAppAuthenticationSecret())->toBeNull();

    $user->saveAppAuthenticationSecret('secret123');
    expect($user->getAppAuthenticationSecret())->toBe('secret123');
});

test('user can get app authentication holder name', function (): void {
    $user = new User(['email' => 'test@example.com']);
    expect($user->getAppAuthenticationHolderName())->toBe('test@example.com');
});

test('user can manage app authentication recovery codes', function (): void {
    $user = User::factory()->create();

    expect($user->getAppAuthenticationRecoveryCodes())->toBeNull();

    $codes = ['code1', 'code2'];
    $user->saveAppAuthenticationRecoveryCodes($codes);

    expect($user->getAppAuthenticationRecoveryCodes())->toBe($codes);
});

test('user can manage email authentication', function (): void {
    $user = User::factory()->create();

    expect($user->hasEmailAuthentication())->toBeFalse();

    $user->toggleEmailAuthentication(true);
    expect($user->hasEmailAuthentication())->toBeTrue();

    $user->toggleEmailAuthentication(false);
    expect($user->hasEmailAuthentication())->toBeFalse();
});
