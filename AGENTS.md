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

===

<laravel-boost-guidelines>
=== .ai/architecture rules ===

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

=== .ai/filament rules ===

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

=== .ai/livewire rules ===

# Livewire Guidelines

## Component Types

### Full-Page Components (Routed)

Live in `App\Livewire\Pages\` or feature-specific directories like `App\Livewire\Auth\`.
Use the `#[Layout]` attribute to specify the layout.

```php
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    // Full-page component
}
```

### Nested Components

Live in `App\Livewire\` organized by feature. Used inside full-page components for reusable interactive sections.

## Navigation

Use `wire:navigate` on all internal navigation links. This enables SPA-like page transitions without full page reloads.

```blade
<a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
```

## Component Conventions

1. **Validate early.** Use `#[Validate]` attributes for simple rules, `$this->validate()` for complex ones.
2. **Typed properties.** Every public property must have a type declaration.
3. **Events over tight coupling.** Use `$this->dispatch()` for communication between components.
4. **Keep components focused.** If a component does more than one thing, split it.
5. **No business logic in components.** Components handle UI state and delegate to Actions for business logic.

## Flux UI Components

Use Flux components as the default UI kit. Only create custom components when Flux genuinely cannot handle the use case. Document the justification in a code comment when creating a custom component.

```blade
{{-- Prefer this --}}
<flux:button wire:click="save">Save</flux:button>

{{-- Over hand-rolled buttons --}}
<button class="..." wire:click="save">Save</button>
```

=== .ai/testing rules ===

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

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== filament/filament/core rules ===

## Filament

- Filament is a Laravel UI framework built on Livewire, Alpine.js, and Tailwind CSS. UIs are defined in PHP via fluent, chainable components. Follow existing conventions in this app.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Inspect required options before running, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `Set $set` inside `->afterStateUpdated()` on a `->live()` field to mutate another field reactively. Prefer `->live(onBlur: true)` on text inputs to avoid per-keystroke updates:

<code-snippet name="Reactive field update" lang="php">
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

TextInput::make('title')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
        'slug',
        Str::slug($state ?? ''),
    )),

TextInput::make('slug')
    ->required(),

</code-snippet>

Compose layout by nesting `Section` and `Grid`. Children need explicit `->columnSpan()` or `->columnSpanFull()`:

<code-snippet name="Section and Grid layout" lang="php">
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

Section::make('Details')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('first_name')
                ->columnSpan(1),
            TextInput::make('last_name')
                ->columnSpan(1),
            TextInput::make('bio')
                ->columnSpanFull(),
        ]),
    ]),

</code-snippet>

Use `Repeater` for inline `HasMany` management. `->relationship()` with no args binds to the relationship matching the field name:

<code-snippet name="Repeater for HasMany" lang="php">
use Filament\Forms\Components\Repeater;

Repeater::make('qualifications')
    ->relationship()
    ->schema([
        TextInput::make('institution')
            ->required(),
        TextInput::make('qualification')
            ->required(),
    ])
    ->columns(2),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Use `SelectFilter` for enum or relationship filters, and `Filter` with a `->query()` closure for custom logic:

<code-snippet name="Table filters" lang="php">
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

SelectFilter::make('status')
    ->options(UserStatus::class),

SelectFilter::make('author')
    ->relationship('author', 'name'),

Filter::make('verified')
    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

</code-snippet>

Actions are buttons that encapsulate optional modal forms and behavior:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data)),

</code-snippet>

### Testing

Testing setup (requires `pestphp/pest-plugin-livewire` in `composer.json`):

- Always call `$this->actingAs(User::factory()->create())` before testing panel functionality.
- For edit pages, pass `['record' => $user->id]`, use `->call('save')` (not `->call('create')`), and do not assert `->assertRedirect()` (edit pages do not redirect after save).

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertHasNoFormErrors()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Edit resource test" lang="php">
livewire(EditUser::class, ['record' => $user->id])
    ->fillForm(['name' => 'Updated'])
    ->call('save')
    ->assertNotified()
    ->assertHasNoFormErrors();

assertDatabaseHas(User::class, [
    'id' => $user->id,
    'name' => 'Updated',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

Use `->callAction(DeleteAction::class)` for page actions, or `->callAction(TestAction::make('name')->table($record))` for table actions:

<code-snippet name="Calling actions" lang="php">
use Filament\Actions\Testing\TestAction;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, `Repeater`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Table columns (`TextColumn`, `IconColumn`, etc.): `Filament\Tables\Columns\`
- Table filters (`SelectFilter`, `Filter`, etc.): `Filament\Tables\Filters\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, `Fieldset`, and `Repeater` do not span all columns by default.
- **Use `Select::make('author_id')->relationship('author', 'name')` for BelongsTo fields.** `BelongsToSelect` does not exist in v4.
- **`Repeater` uses `->schema()`, not `->fields()`.**
- **Never add `->dehydrated(false)` to fields that need to be saved.** It strips the value from form state before `->action()` or the save handler runs. Only use it for helper/UI-only fields.
- **Use correct property types when overriding `Page`, `Resource`, and `Widget` properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on `Page` and `Widget` classes

</laravel-boost-guidelines>
