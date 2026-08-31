# AGENTS.md — Anvil Starter Kit

> **Canonical AI-agent source of truth.** All other agent guideline files (`CLAUDE.md`, `GEMINI.md`, `.cursor/rules/*`) are mirrors of this file. Edit only this file; run `php artisan boost:install` to propagate changes.

## Project Overview

Anvil is a Laravel 13 + Livewire 4 + Filament 5 starter kit. It provides a Livewire-powered customer-facing app and a Filament admin panel in a single repo, with enforced 100% type coverage and 100% test coverage.

## Tech Stack

- **PHP 8.3+** (8.4 recommended)
- **Laravel 13** with `nunomaduro/essentials` (strict models, immutable dates, auto eager-load)
- **Livewire 4** with Flux UI components for the customer-facing app
- **Filament 5** for the admin panel (path: `/admin`, SPA mode)
- **Pest** for testing with type-coverage and line-coverage plugins
- **Larastan** (PHPStan) at level 9
- **Rector** with `driftingly/rector-laravel` rules
- **Laravel Pint** with the `laravel` preset
- **Laravel Boost** for AI guidelines and MCP

## Architecture Rules

1. **Actions over services.** Business logic lives in single-purpose, invokable `App\Actions\*` classes. Do not create service classes unless there is a justified, documented reason.
2. **No magic.** Prefer explicit code over clever abstractions. A method that needs a comment to explain *what* it does should be renamed, not commented.
3. **Immutable by default.** Use `readonly` properties and `CarbonImmutable` (provided by Essentials).
4. **Simple RBAC.** The `App\Enums\Role` enum (`admin`/`user`) plus Gate definitions handle authorization. Do not add Spatie Permission or Filament Shield unless explicitly requested.
5. **Filament panel is admin-only.** `canAccessPanel()` checks `$user->role === Role::Admin`.

## Code Quality Requirements

Every PR must pass `composer review`, which runs:

```bash
composer lint            # Pint — zero diff
composer refactor        # Rector — zero diff (dry-run)
composer types           # Larastan level 9 — zero errors
composer test:type-coverage  # 100% type coverage
composer test:unit       # 100% line coverage
```

## Testing Rules

1. **Every Filament Resource** must have a Pest smoke test covering: list page loads, create works, edit works, delete works.
2. **Every Livewire component** must have at least one `Livewire::test()` assertion.
3. **No coverage-only tests.** Every test must assert meaningful behavior, not just execute lines to make coverage green.
4. **Use `RefreshDatabase`** in feature tests (already configured in `Pest.php`).

## File Organization

```
app/
  Actions/         # Invokable business logic classes
  Enums/           # Backed enums (Role, etc.)
  Filament/        # Admin panel resources, pages, widgets
  Livewire/        # Customer-facing components
    Auth/           # Authentication components
    Settings/       # User settings components
    Actions/        # Livewire action classes (Logout, etc.)
  Models/          # Eloquent models
  Providers/       # Service providers
tests/
  Unit/            # Pure PHP tests (enums, actions, value objects)
  Feature/         # HTTP + Livewire::test() + Filament tests
```

## Coding Conventions

- Always use `declare(strict_types=1)` at the top of every PHP file
- Always type-hint return types and parameter types
- Use PHP 8.3+ features: enums, readonly properties, match expressions, named arguments
- Prefer `fn()` arrow functions for single-expression closures
- Use `snake_case` for database columns, `camelCase` for PHP properties/methods
- Route model binding over manual `find()` calls

## UI/UX Conventions

- Flux UI components are the default; custom components only when Flux genuinely cannot do it
- Tailwind design tokens defined in config, referenced everywhere — no ad-hoc `px-[13px]`
- Every list view must have a defined empty state and loading state
- Every destructive action requires confirmation
- Dark mode toggle is cookie-persisted, available in both layouts
