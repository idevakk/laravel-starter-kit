# Architecture Guidelines

## Actions Pattern

Business logic lives in single-purpose, invokable Action classes under `App\Actions\`.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class CreateUser
{
    public function __invoke(string $name, string $email, string $password): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
```

### When to use an Action

- The logic is reused across controllers, Livewire components, Filament resources, or artisan commands
- The operation has side effects (database writes, API calls, notifications)
- The logic is complex enough that inlining it obscures the calling code

### When NOT to extract to an Action

- Simple CRUD operations that are trivially inline (a single `Model::create()` or `->update()`)
- Logic that is only ever used in one place and is ≤5 lines

## No Service Layer for Its Own Sake

Do not create `App\Services\UserService` just because "services are a pattern." If you have multiple related actions, they can coexist as separate Action classes. A service class is justified only when:

1. It encapsulates a third-party SDK integration (e.g., `StripeService`)
2. Multiple actions share state or configuration that would be duplicated otherwise

## Model Conventions

- `readonly` properties where possible
- Explicit `$fillable` arrays (no `$guarded = []`)
- Enums for columns with a fixed set of values (`Role`, `Status`, etc.)
- Scopes for reusable query constraints
- Relationships type-hinted in PHPDoc for IDE/static analysis
