# Testing Guidelines

## What "Done" Means

A feature is not done until:

1. **Type coverage** — `pest --type-coverage --min=100` passes. Every function, method, property, and parameter has a type declaration.
2. **Line coverage** — `pest --coverage --min=100` passes. Every line of application code is exercised by at least one test.
3. **Meaningful assertions** — Every test asserts behavior, not just execution. A test that calls a method without checking the result is not a test.

## Test Organization

```
tests/
  Unit/           # Pure PHP: Enums, Actions, value objects, helpers
  Feature/        # HTTP requests, Livewire::test(), Filament resource tests
  Browser/        # Pest browser tests (Playwright) — smoke-level only
  Pest.php        # Global test configuration
```

## Rules

### No Coverage-Only Tests
❌ Bad — runs code but asserts nothing meaningful:
```php
it('exists', function () {
    expect(true)->toBeTrue();
});
```

✅ Good — asserts actual behavior:
```php
it('creates a user with the admin role', function () {
    $user = User::factory()->admin()->create();
    expect($user->role)->toBe(Role::Admin);
});
```

### Feature Tests Use RefreshDatabase
The `RefreshDatabase` trait is already configured in `Pest.php` for the `Feature` directory. Every feature test gets a clean database.

### Livewire Component Testing
```php
use Livewire\Livewire;

it('can update profile information', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name');
});
```

### Filament Resource Testing
Every Filament resource must have smoke tests for list, create, edit, and delete operations. See `.ai/guidelines/filament.md` for the template.

### Browser Tests (Stretch Goal)
Browser tests use Pest's native Playwright integration. Keep them intentionally small — just critical paths:
- Auth flow (register → verify → login)
- One representative Livewire page interaction
- One Filament CRUD flow
