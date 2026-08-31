# Filament Guidelines

## Panel Configuration

The admin panel uses:
- Panel ID: `admin`, path: `/admin`
- SPA mode enabled
- MFA via TOTP (AppAuthentication)
- Custom theme sharing Tailwind design tokens with the Livewire app

## Resource Conventions

### Naming
- Resource class: `UserResource` (singular model name + `Resource`)
- Directory: `App\Filament\Resources\UserResource\`
- Pages: `ListUsers`, `CreateUser`, `EditUser`

### Required Components
Every resource must define:
1. `form()` — the create/edit form schema
2. `table()` — the list table with columns, filters, and actions
3. Appropriate `getPages()` method registering list, create, and edit pages

### Mandatory Testing Rule

**Every generated Filament resource must ship with a Pest smoke test before it is considered done.** The test must cover:

```php
it('can list users', function () {
    $this->get(UserResource::getUrl('index'))
        ->assertSuccessful();
});

it('can create a user', function () {
    // Test the create form
});

it('can edit a user', function () {
    // Test the edit form with existing data
});

it('can delete a user', function () {
    // Test the delete action
});
```

This mirrors Filament's own published AI guideline for Boost-driven resource generation.

## Custom Pages

Use `Filament\Pages\Page` for custom admin pages. Keep them in `App\Filament\Pages\`.

## Widgets

Dashboard widgets live in `App\Filament\Widgets\`. Use `StatsOverviewWidget` for key metrics and `ChartWidget` for trends.
