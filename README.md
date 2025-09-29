# 🚀 Laravel Filament Admin Starter Kit

A pre-configured **Laravel Admin Panel Starter Kit** built with [Livewire Starter Kit](https://github.com/laravel-livewire/starter-kit) and powered by [FilamentPHP](https://filamentphp.com/).

Ideal for developers who want to skip boilerplate setup and jump straight into building robust admin dashboards, with essential tools and permissions already in place.

---

## ⚡ Features at a Glance

* ✅ Livewire Starter Kit foundation
* ✅ Elegant UI with **FilamentPHP**
* ✅ Filament Panel configurable via `.env`
* ✅ Roles & Permissions (Spatie)
* ✅ Activity Logging
* ✅ Mail & Log Viewer
* ✅ **Database Config Manager (via Filament Plugin)**
* ✅ Code quality with **RectorPHP**
* ✅ Authentication with default seeded admin

---

## 📂 Admin Panel Access

* **Panel ID:** `dash`
* **Admin Route:** `/dash` (configurable)
* **Default Admin Credentials:**

| Email               | Password   |
| ------------------- | ---------- |
| `admin@example.com` | `password` |

> ⚠️ **Change these credentials before deploying to production.**

---

## ⚙️ Environment Configuration

All major features are easily configurable using environment variables:

```env
# Filament Admin Panel
FILAMENT_ROUTE=dash
FILAMENT_ENABLE_LOGIN=true
FILAMENT_ENABLE_REGISTER=false
FILAMENT_ENABLE_PROFILE=false

# Activity Logger
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_TABLE_NAME=activity_log

# Log Viewer
FILAMENT_LOG_VIEWER_DRIVER=raw
```

---

## 🎨 Filament Panel Configuration

The Filament admin panel's appearance and basic behaviors are defined in `config/filament-php.php`:

```php
<?php

use Filament\Support\Colors\Color;

return [
    'route' => env('FILAMENT_ROUTE', 'dash'),

    'colors' => [
        'primary' => Color::Teal,
        'danger' => Color::Red,
        'gray'    => Color::Zinc,
        'info'    => Color::Blue,
        'success' => Color::Green,
        'warning' => Color::Amber,
    ],

    'enable_login'        => env('FILAMENT_ENABLE_LOGIN', true),
    'enable_registration' => env('FILAMENT_ENABLE_REGISTER', false),
    'enable_profile'      => env('FILAMENT_ENABLE_PROFILE', false),
];
```

You can easily override the route, theming colors, and feature toggles via `.env`.

---

## 🔐 Roles & Permissions

This starter kit uses [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) with the following default setup:

### 👤 Roles

* `admin` – Full control, including access to logs and mails
* `user` – No permissions assigned by default

### 🔑 Permissions

The `admin` role is granted the following permissions:

* `manage mails`
* `manage panels`

These are assigned in the `RoleSeeder` class:

```php
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::findOrCreate(name: 'admin', guardName: 'web');
        Role::findOrCreate(name: 'user', guardName: 'web');

        $permissionManageMails = Permission::findOrCreate(name: 'manage mails', guardName: 'web');
        $role->givePermissionTo($permissionManageMails);

        $permissionManageFilamentPanel = Permission::findOrCreate(name: 'manage panels', guardName: 'web');
        $role->givePermissionTo($permissionManageFilamentPanel);
    }
}
```

> ✅ You can expand this by adding more roles and permissions via the Filament UI or custom seeders.

---

## 🛠️ Manage Config from Admin Panel (Inerba DB Config Plugin)

This starter kit includes the [**Filament DB Config Plugin**](https://filamentphp.com/plugins/inerba-db-config) by **Inerba**, which allows you to **store application config settings in the database** and manage them from the admin panel.

### 🔧 Features

* Create configuration keys and values dynamically
* Group and categorize settings
* Easily retrieve values via `config('db-config.key')`
* No code deployment needed for config changes

### 🧩 Usage

After installing and publishing the plugin:

1. Visit `/dash/db-config` in your Filament panel
2. Add new config entries (e.g. `site.name`, `maintenance_mode`, etc.)
3. Use in code like so:

```php
$value = config('db-config.site.name');
```

> ℹ️ Useful for managing site-wide settings, feature toggles, or any admin-editable config.

---

## 📨 Mail Viewer

View all outgoing mail inside the Filament admin panel — ideal for local and staging environments.

---

## 🪵 Log Viewer

Filament Log Viewer is pre-configured to use the `raw` driver:

```env
FILAMENT_LOG_VIEWER_DRIVER=raw
```

View and analyze application logs from the admin panel.
For advanced configuration, see the [Log Viewer docs](https://github.com/ryangjchandler/filament-log).

---

## 🕵️ Activity Logger

Tracks user actions and stores them in the `activity_log` table (or custom name via `.env`):

```env
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_TABLE_NAME=activity_log
```

More configuration options available in [Spatie’s documentation](https://spatie.be/docs/laravel-activitylog).

---

## 🧼 Automated Refactoring with Rector

The root of the project includes a pre-configured `rector.php` file.
This allows for auto-refactoring and upgrading code to Laravel best practices.

### ➕ Run Rector:

```bash
vendor/bin/rector
```

---

## 🚀 Installation

```bash
git clone https://github.com/idevakk/starter-kit.git your-project
cd your-project

composer install
cp .env.example .env
php artisan key:generate
```

Update your `.env` with database and mail configuration:

```bash
php artisan migrate --seed
npm install && npm run dev
php artisan serve
```

Visit: [http://localhost:8000/dash](http://localhost:8000/dash) (or your configured route)

---

## 📁 Key Structure

```
├── config/
│   ├── filament.php            # Filament core settings
│   ├── filament-php.php        # UI theming + custom toggles
│   ├── activitylog.php         # Activity log config
│   └── db-config.php           # (Optional) Plugin config
├── database/
│   └── seeders/FilamentAdminSeeder.php
│   └── seeders/RoleSeeder.php
├── rector.php                  # Rector config
```

---

## 🤝 Contributing

Contributions are welcome. Feel free to fork, submit issues, or create pull requests.

---

## 🛡 License

Open-sourced under the [MIT license](LICENSE).

---

## 🙏 Credits

* [Laravel](https://laravel.com)
* [Livewire Starter Kit](https://github.com/laravel-livewire/starter-kit)
* [FilamentPHP](https://filamentphp.com/)
* [Spatie Permissions](https://github.com/spatie/laravel-permission)
* [Spatie Activity Log](https://spatie.be/docs/laravel-activitylog)
* [Filament Log Viewer](https://github.com/ryangjchandler/filament-log)
* [Filament Mail Viewer](https://github.com/ryangjchandler/filament-mail)
* [Inerba DB Config Plugin](https://filamentphp.com/plugins/inerba-db-config)
* [RectorPHP](https://getrector.com)

---
