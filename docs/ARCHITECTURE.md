# Architecture

## Overview

Anvil is a Laravel 13 monolithic application with two distinct frontend layers:

1. **Customer-facing app** — Built with Livewire 4 + Flux UI components, routed via standard Laravel routes
2. **Admin panel** — Built with Filament 5, available at `/admin`, SPA mode enabled

Both share the same Eloquent models, database, and business logic (via Actions). They share the same Tailwind v4 installation but use separate entry points to avoid CSS conflicts.

## Directory Structure

```
app/
├── Actions/          # Invokable business logic classes
├── Enums/            # Backed enums (Role, etc.)
├── Filament/         # Admin panel (Filament 5)
│   ├── Resources/    # CRUD resources
│   ├── Pages/        # Custom admin pages
│   └── Widgets/      # Dashboard widgets
├── Livewire/         # Customer-facing app (Livewire 4)
│   ├── Auth/         # Authentication components
│   ├── Settings/     # User settings components
│   ├── Actions/      # Livewire action classes
│   └── Pages/        # Full-page routed components
├── Models/           # Eloquent models
├── Policies/         # Authorization policies
└── Providers/        # Service providers
    └── Filament/     # Filament panel providers
```

## Authorization

Uses a simple role-enum gate instead of a full RBAC package:

- `App\Enums\Role` — `admin` and `user` values
- `canAccessPanel()` on the User model checks for `Role::Admin`
- Gate definitions for fine-grained authorization as needed

### Upgrade Path (documented, not implemented)

For teams needing per-resource, per-action permissions:
1. Add `spatie/laravel-permission` and `bezhansalleh/filament-shield`
2. Replace the `role` enum column with Spatie's role/permission tables
3. Update `canAccessPanel()` to use `hasPermissionTo('manage panels')`

## Quality Enforcement

All of the following must pass before any code is merged:

| Check | Command | Threshold |
|-------|---------|-----------|
| Code style | `composer lint` | Zero diff |
| Refactoring | `composer refactor` | Zero diff (dry-run) |
| Static analysis | `composer types` | Level 9, zero errors |
| Type coverage | `composer test:type-coverage` | 100% |
| Test coverage | `composer test:unit` | 100% |

Run all at once: `composer review`
