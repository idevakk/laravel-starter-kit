# ⚡ Anvil — Laravel 13 + Livewire 4 + Filament 5 Starter Kit

A production-grade, bloat-free Laravel 13 starter kit combining a **Livewire 4 + Flux UI** customer application and a **Filament 5** admin panel in a single repository — backed by 100% type coverage, 100% test coverage, and comprehensive AI agent guidelines via **Laravel Boost**.

---

## ⚡ Features & Highlights

- 🚀 **Modern Core**: Laravel 13 running on PHP 8.3+ (8.4 recommended) with `nunomaduro/essentials` (strict models, immutable dates with `CarbonImmutable`, automatic eager loading, safe console).
- 🎨 **Customer Frontend**: Built with **Livewire 4** and **Flux UI** components with full authentication flows (login, registration, password reset, email verification) and user settings (profile, password, cookie-persisted dark mode).
- 🎛️ **Admin Panel**: **Filament 5** admin panel located at `/admin` (SPA mode enabled), with built-in Multi-Factor Authentication (TOTP AppAuthentication) and role-gated access.
- 🛡️ **Clean & Lightweight RBAC**: Backed `Role` enum (`admin` / `user`) + Gate authorization via `canAccessPanel()` — zero bloat from third-party permission packages.
- 🧪 **100% Quality Enforcement**:
  - **PHPStan / Larastan** at Level 9
  - **Pest 5** with **100% Type Coverage** (`pest-plugin-type-coverage`)
  - **Pest 5** Unit & Feature test suite (`pest-plugin-laravel`, `pest-plugin-livewire`)
  - **Laravel Pint** with strict Laravel preset
  - **Rector** + `driftingly/rector-laravel` for automated upgrades
- 🤖 **AI-Agent Ready**: Canonical agent instructions in `AGENTS.md`, modular area rules in `.ai/rules/`, and full **Laravel Boost** MCP tooling for Claude Code, Cursor, Windsurf, Gemini, and GitHub Copilot.
- 🛠️ **Unified Dev Experience**: One-command project setup (`composer setup`) and a multi-process dev runner (`composer dev`) powering server, queue listener, Pail log tailing, and Vite concurrently.

---

## 📦 Tech Stack

| Layer | Technology / Package | Description |
|---|---|---|
| **Framework** | [Laravel 13](https://laravel.com) | Core PHP web application framework |
| **Runtime** | PHP 8.3+ (8.4 recommended) | Strict typing with `declare(strict_types=1)` |
| **Frontend Reactive** | [Livewire 4](https://livewire.laravel.com) | Reactive customer-facing UI |
| **UI Components** | [Flux UI](https://fluxui.dev) | Accessible, beautiful UI component primitives |
| **Admin Panel** | [Filament 5](https://filamentphp.com) | Admin panel located at `/admin` in SPA mode |
| **Styling** | [Tailwind CSS v4](https://tailwindcss.com) | Unified design tokens across frontend & admin |
| **Defaults** | `nunomaduro/essentials` | Strict models, immutable dates, safe console |
| **Testing** | [Pest 5](https://pestphp.com) | Test runner with Livewire & Type Coverage plugins |
| **Static Analysis** | [Larastan 3](https://github.com/larastan/larastan) | PHPStan at Level 9 |
| **Code Style** | [Laravel Pint](https://laravel.com/docs/pint) | Automated code style enforcement |
| **Refactoring** | [Rector 2](https://getrector.com) | Automated modernization & Laravel rule sets |
| **AI Guidelines** | [Laravel Boost 2](https://github.com/laravel/boost) | AI agent guidelines, MCP server, and rule engine |

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.3 (with `sqlite3`, `curl`, `mbstring`, `openssl`, `pdo_sqlite` extensions)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (v20+) & npm

### Quick Setup

Clone the repository and run the automated setup command:

```bash
git clone https://github.com/idevakk/laravel-starter-kit.git my-app
cd my-app

# Installs dependencies, sets up .env, generates key, migrates & seeds SQLite db, builds assets
composer setup
```

Start the entire development environment (HTTP server, queue listener, Laravel Pail log viewer, Vite):

```bash
composer dev
```

---

### Manual Installation

If you prefer running the setup steps manually:

```bash
# 1. Install PHP & Node dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Initialize SQLite database & seed default data
touch database/database.sqlite
php artisan migrate --seed

# 4. Build assets & run dev server
npm run build
composer dev
```

---

## 🔑 Access & Default Credentials

| Portal | URL | Description |
|---|---|---|
| **Customer App** | [http://localhost:8000](http://localhost:8000) | Livewire 4 frontend (`/dashboard`, settings, auth) |
| **Admin Panel** | [http://localhost:8000/admin](http://localhost:8000/admin) | Filament 5 admin panel (SPA mode, MFA supported) |

### Default Admin Credentials

- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** `Role::Admin`

> ⚠️ **Important:** Change the default admin credentials and configure TOTP MFA before deploying to production.

---

## 🏗️ Architecture & Conventions

### 1. Actions Over Services
Business logic is organized into single-purpose, invokable Action classes under `app/Actions/` (e.g. `CreateUser`, `UpdatePassword`). Avoid monolithic service classes unless wrapping external third-party SDKs.

### 2. Immutable by Default & Strict Typing
- Every PHP file begins with `declare(strict_types=1)`.
- Explicit parameter and return type declarations on all functions and methods.
- Immutable dates via `CarbonImmutable` and `readonly` properties wherever applicable.

### 3. Lightweight Role-Based Access Control (RBAC)
Authorization is handled via the `App\Enums\Role` enum (`admin` / `user`) and standard Laravel Gates:
```php
// User model check for Filament admin panel access
public function canAccessPanel(Panel $panel): bool
{
    return $this->role === Role::Admin;
}
```

### 4. Flux UI Design Consistency
Customer UI is built using Flux components (`<flux:button>`, `<flux:input>`, `<flux:modal>`, etc.) with Tailwind design tokens. Avoid ad-hoc utility hacks (`px-[13px]`) and maintain consistent empty and loading states.

---

## 🧪 Quality & Testing Commands

Anvil enforces a strict quality bar. Every change should pass the complete review suite:

```bash
composer review
```

This runs all checks sequentially:

| Command | Tool | Purpose / Standard |
|---|---|---|
| `composer lint` | Laravel Pint | Code styling verification (zero diff) |
| `composer refactor` | Rector | Modernization & dead-code dry run |
| `composer types` | Larastan (PHPStan) | Static analysis at **Level 9** |
| `composer test:type-coverage` | Pest Plugin | Enforces **100% type coverage** |
| `composer test:unit` | Pest 5 | Feature and unit test execution (100% line coverage) |
| `composer test` | Artisan | Clears config cache and runs full test suite |

### Individual Tool Runners

```bash
# Format code with Pint
./vendor/bin/pint

# Run Rector refactoring
./vendor/bin/rector

# Run specific Pest tests
php artisan test --filter=AuthenticationTest
```

---

## 📂 Project Structure

```
app/
├── Actions/              # Invokable business logic classes
├── Enums/                # Backed enums (Role::Admin, Role::User)
├── Filament/             # Filament 5 admin resources, pages, and widgets
│   ├── Pages/
│   ├── Resources/
│   └── Widgets/
├── Http/
│   └── Controllers/      # Standard HTTP and Auth controllers
├── Livewire/             # Customer-facing Livewire 4 components
│   ├── Actions/          # Livewire-specific actions (e.g., Logout)
│   ├── Auth/             # Login, Register, Password Reset, Verification
│   └── Settings/         # Profile, Password, Appearance
├── Models/               # Eloquent models with strict typing
└── Providers/
    ├── AppServiceProvider.php
    └── Filament/
        └── AdminPanelProvider.php  # Admin panel configuration (/admin)
database/
├── factories/            # Model factories
├── migrations/           # Database schema migrations
└── seeders/
    ├── AdminSeeder.php   # Default admin user seeder
    └── DatabaseSeeder.php
docs/
├── ARCHITECTURE.md       # Architecture & extension guides
└── TASTE.md              # Design, UI/UX, and code taste guidelines
routes/
├── console.php           # Artisan console routes
└── web.php               # Customer web & authentication routes
tests/
├── Feature/              # Feature tests (Auth, Dashboard, Settings, Filament)
└── Unit/                 # Unit tests (Enums, Models, Actions)
```

---

## 🤖 AI Coding Guidelines (Laravel Boost)

Anvil is designed from the ground up for seamless pairing with AI coding agents:
- **`AGENTS.md`**: Canonical, single source of truth for architecture and development rules.
- **`.ai/rules/`**: Scoped rules and constraints automatically referenced by AI agents during coding.
- **Laravel Boost**: Integrated MCP server providing documentation search, database introspection, and guidelines.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/idevakk/laravel-starter-kit/issues).

---

## 🛡️ License

This project is open-sourced software licensed under the [MIT license](LICENSE).
