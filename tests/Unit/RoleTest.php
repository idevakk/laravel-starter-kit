<?php

declare(strict_types=1);

use App\Enums\Role;

test('role enum has correct values', function (): void {
    expect(Role::Admin->value)->toBe('admin')
        ->and(Role::User->value)->toBe('user');
});
