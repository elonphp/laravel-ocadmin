# Laravel Ocadmin Modules

A modular Laravel admin panel framework.

## Features

- **Modular Architecture** - Separate management of standard and custom modules
- **Multi-language Routing** - URL prefix-based locale switching (`/{locale}/ocadmin/...`)
- **Laravel Style** - Directory structure follows Laravel conventions
- **Override Mechanism** - Views, routes, and configurations can be overridden at project level
- **Independent Updates** - Package updates don't affect project customizations

## Documentation

| Document | Description |
|----------|-------------|
| [README.md](README.md) | Installation and Quick Start |
| [docs/localization-overview.md](docs/localization-overview.md) | Localization Overview |
| [docs/localization-url.md](docs/localization-url.md) | URL Localization |
| [docs/localization-interface.md](docs/localization-interface.md) | Interface Localization |
| [docs/localization-content.md](docs/localization-content.md) | Content Localization |
| [docs/modules.md](docs/modules.md) | Module Development Guide |
| [docs/customization.md](docs/customization.md) | Customization Mechanism |

---

## Installation

```bash
composer require elonphp/laravel-ocadmin-modules
```

After installation, it's **ready to use** immediately without copying any files. All features are loaded from `vendor/`.

---

## Copy on Demand

This package adopts a "copy on demand" strategy:

| Requirement | Command | Description |
|-------------|---------|-------------|
| Basic usage | No command needed | Load directly from vendor |
| Override config | `vendor:publish --tag=ocadmin-config` | Publish to `config/ocadmin.php` |
| Customization | `ocadmin:init` | Initialize `app/Ocadmin/` directory structure |

### Project File Structure (Generated on Demand)

```
Project Root/
├── vendor/elonphp/laravel-ocadmin-modules/   # Package (always present)
│
├── config/
│   └── ocadmin.php                            # (Optional) Override config
│
└── app/
    └── Ocadmin/                               # (Optional) Customization directory
        ├── Modules/                           # Custom modules
        ├── Resources/views/                   # Override views
        ├── Config/                            # Additional config
        └── Routes/                            # Additional routes
```

All customizations are centralized in the `app/Ocadmin/` directory.

---

## Directory Structure

### src/Core/ Folder Description

| Folder | Purpose | Description |
|--------|---------|-------------|
| `Providers/` | Service Providers | Main entry point for the package, registers services, routes, views, etc. |
| `Controllers/` | HTTP Controllers | Core controllers (Login, Dashboard, etc.) |
| `Middleware/` | Middleware | Request handling middleware (locale setting, redirects, etc.) |
| `Support/` | Support Classes | Core classes that are not Controller/Middleware/Provider |
| `ViewComposers/` | View Composers | Auto-inject variables into views (e.g., menu data) |
| `Console/` | Artisan Commands | CLI commands (init, module, list) |
| `Traits/` | Shared Traits | Reusable PHP Traits |

#### Support/ Folder Contents

| Class | Purpose |
|-------|---------|
| `ModuleLoader.php` | Module loader: scans, loads, and registers standard and custom modules |

#### ViewComposers/ Folder Contents

| Class | Purpose |
|-------|---------|
| `MenuComposer.php` | Menu composer: auto-injects `$menus` variable into sidebar view |

### src/Support/ Folder Description

| Class | Purpose |
|-------|---------|
| `LocaleHelper.php` | Locale helper: URL/internal format conversion, locale setting, switch link generation |

### Package Structure (vendor, do not modify)

```
vendor/elonphp/laravel-ocadmin-modules/
├── src/
│   ├── Core/                           # Core framework
│   │   ├── Providers/                  # Service providers
│   │   │   └── OcadminServiceProvider.php
│   │   ├── Controllers/                # HTTP controllers
│   │   │   ├── Controller.php
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   ├── Middleware/                 # Middleware
│   │   │   ├── SetLocale.php
│   │   │   └── RedirectToLocale.php
│   │   ├── Support/                    # Support/helper classes
│   │   │   └── ModuleLoader.php
│   │   ├── ViewComposers/              # View composers
│   │   │   └── MenuComposer.php
│   │   ├── Console/                    # Artisan commands
│   │   │   ├── InitCommand.php
│   │   │   ├── ModuleCommand.php
│   │   │   └── ListCommand.php
│   │   └── Traits/                     # Shared traits
│   │
│   ├── Support/                        # Global support classes
│   │   └── LocaleHelper.php
│   │
│   └── Modules/                        # Standard modules
│       ├── SystemLog/
│       ├── AccessControl/
│       ├── Taxonomy/
│       ├── Localization/
│       ├── Setting/
│       └── MetaKey/
│
├── config/
│   └── ocadmin.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── partials/
│       ├── components/
│       └── errors/
│
├── routes/
│   └── ocadmin.php
│
├── database/
│   └── migrations/
│
└── stubs/                              # Skeleton templates
    └── module/
```

### Project Structure (app/Ocadmin/, created on demand)

Generated after running `php artisan ocadmin:init`:

```
app/
└── Ocadmin/
    ├── Modules/                        # Custom modules directory
    │   └── {ModuleName}/               # Created by ocadmin:module
    │       ├── Controllers/
    │       ├── Models/
    │       ├── Views/
    │       ├── Routes/
    │       │   └── routes.php
    │       ├── Config/
    │       │   └── menu.php
    │       └── module.json
    │
    ├── Resources/
    │   └── views/                      # Override views
    │       ├── layouts/                # Override shared layouts
    │       ├── components/             # Override shared components
    │       └── modules/                # Override standard module views
    │           └── system-log/
    │
    ├── Config/                         # Additional config
    │   └── menu.php                    # Additional menu
    │
    └── Routes/                         # Additional routes
        └── routes.php
```

---

## Loading Priority

### Module Loading Order

```
1. vendor/.../src/Core/              # Core framework
2. vendor/.../src/Modules/           # Standard modules (built-in)
3. app/Ocadmin/Modules/              # Custom modules (project-defined)
```

### View Priority (later overrides earlier)

```
1. vendor/.../resources/views/              # Package shared views
2. vendor/.../src/Modules/{Name}/Views/     # Standard module views
3. app/Ocadmin/Resources/views/             # Project override (priority)
4. app/Ocadmin/Modules/{Name}/Views/        # Custom module views
```

### Config Priority

```
1. vendor/.../config/ocadmin.php            # Package default
2. config/ocadmin.php                       # Project override (mergeConfigFrom)
```

---

## Creating Custom Modules

Use Artisan command:

```bash
php artisan ocadmin:module Inventory
```

Generated structure:

```
app/Ocadmin/Modules/Inventory/
├── Controllers/
│   └── InventoryController.php
├── Models/
├── Views/
│   └── index.blade.php
├── Routes/
│   └── routes.php
├── Config/
│   └── menu.php
└── module.json
```

### module.json Format

```json
{
    "name": "Inventory",
    "description": "Inventory management module",
    "priority": 50,
    "enabled": true
}
```

- `priority`: Loading order, lower numbers load first
- `enabled`: Whether enabled

---

## Override Mechanism

### View Types

| Type | Package Location | Override Location |
|------|------------------|-------------------|
| Shared layouts | `vendor/.../resources/views/layouts/` | `app/Ocadmin/Resources/views/layouts/` |
| Shared components | `vendor/.../resources/views/components/` | `app/Ocadmin/Resources/views/components/` |
| Standard module views | `vendor/.../src/Modules/{Name}/Views/` | `app/Ocadmin/Resources/views/modules/{name}/` |
| Custom module views | N/A | `app/Ocadmin/Modules/{Name}/Views/` |

### Override Shared Layouts

Create corresponding views under `app/Ocadmin/Resources/views/layouts/`:

```
# Package view
vendor/.../resources/views/layouts/app.blade.php

# Project override
app/Ocadmin/Resources/views/layouts/app.blade.php
```

### Override Standard Module Views

Create under `app/Ocadmin/Resources/views/modules/{module-name}/`:

```
# Package view
vendor/.../src/Modules/SystemLog/Views/index.blade.php

# Project override
app/Ocadmin/Resources/views/modules/system-log/index.blade.php
```

> Module names use kebab-case (e.g., `system-log`)

### Override Config

```bash
php artisan vendor:publish --tag=ocadmin-config
```

Config file is copied to `config/ocadmin.php`, modifications override package defaults.

### Additional Routes (requires ocadmin:init first)

Define in `app/Ocadmin/Routes/routes.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Ocadmin\Modules\Report\Controllers\ReportController;

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
});
```

### Additional Menu (requires ocadmin:init first)

Define in `app/Ocadmin/Config/menu.php`:

```php
<?php

return [
    [
        'group' => 'custom',
        'title' => 'Custom Features',
        'icon' => 'fa-solid fa-cog',
        'items' => [
            [
                'title' => 'Report',
                'route' => 'report.index',
            ],
        ],
    ],
];
```

---

## View Architecture

### Complete View Paths

```
vendor/elonphp/laravel-ocadmin-modules/
├── resources/views/                        # Package shared views
│   ├── layouts/
│   │   ├── app.blade.php                   # Main framework
│   │   ├── auth.blade.php                  # Login page framework
│   │   └── partials/
│   │       ├── header.blade.php
│   │       ├── sidebar.blade.php
│   │       └── footer.blade.php
│   ├── components/                         # Shared components
│   │   ├── card.blade.php
│   │   ├── modal.blade.php
│   │   └── table.blade.php
│   └── errors/
│
├── src/Modules/
│   └── SystemLog/
│       └── Views/                          # Standard module views
│           ├── index.blade.php
│           └── form.blade.php

---

app/Ocadmin/                                # Project customization
├── Modules/
│   └── Inventory/
│       └── Views/                          # Custom module views
│           └── index.blade.php
│
└── Resources/views/                        # Override package views
    ├── layouts/                            # Override shared layouts
    │   └── app.blade.php
    ├── components/                         # Override shared components
    │   └── card.blade.php
    └── modules/                            # Override standard module views
        └── system-log/
            └── index.blade.php
```

### Blade Reference Methods

```php
{{-- Reference shared layouts --}}
@extends('ocadmin::layouts.app')

{{-- Reference shared components --}}
@include('ocadmin::components.card')

{{-- Reference partials --}}
@include('ocadmin::layouts.partials.sidebar')
```

### Design Principles

| Purpose | Location |
|---------|----------|
| Add custom module | `app/Ocadmin/Modules/{Name}/Views/` |
| Override shared layouts/components | `app/Ocadmin/Resources/views/layouts/` or `components/` |
| Override standard module views | `app/Ocadmin/Resources/views/modules/{name}/` |

---

## Standard Modules

| Module | Description |
|--------|-------------|
| SystemLog | System log query |
| AccessControl | Role and permission management |
| Taxonomy | Taxonomy (Categories, Tags) |
| Localization | Multi-language management |
| Setting | System parameter settings |
| MetaKey | EAV field definitions |

---

## Artisan Commands

### Package Commands

```bash
# Initialize custom module directory structure
php artisan ocadmin:init
```

Creates `app/Ocadmin/` directory skeleton. Since it's under `app/`, Laravel's PSR-4 autoload automatically handles the `App\Ocadmin\` namespace.

---

```bash
# Create a new custom module
php artisan ocadmin:module {name}

# Example
php artisan ocadmin:module Inventory
```

Creates complete module structure under `app/Ocadmin/Modules/`.

---

```bash
# List all loaded modules
php artisan ocadmin:list
```

Output example:
```
+----------------+----------+----------+--------+
| Module         | Source   | Priority | Status |
+----------------+----------+----------+--------+
| SystemLog      | package  | 10       | active |
| AccessControl  | package  | 20       | active |
| Taxonomy       | package  | 30       | active |
| Inventory      | custom   | 50       | active |
+----------------+----------+----------+--------+
```

---

### Laravel Publish Commands

```bash
# Publish config file
php artisan vendor:publish --tag=ocadmin-config
# -> config/ocadmin.php

# Publish migrations
php artisan vendor:publish --tag=ocadmin-migrations
# -> database/migrations/

# Publish static assets
php artisan vendor:publish --tag=ocadmin-assets
# -> public/vendor/ocadmin/

# Publish all assets
php artisan vendor:publish --provider="Elonphp\LaravelOcadminModules\Core\Providers\OcadminServiceProvider"
```

---

### Publish Tags Overview

| Tag | Target Path | Description |
|-----|-------------|-------------|
| `ocadmin-config` | `config/ocadmin.php` | Config file |
| `ocadmin-migrations` | `database/migrations/` | Database migrations |
| `ocadmin-assets` | `public/vendor/ocadmin/` | Static assets (CSS/JS/images) |

> For view overrides, use `app/Ocadmin/Resources/views/` instead of publishing.

---

## Frontend Assets

### Publishing Assets

Publish frontend assets (CSS, JS, images) to your project:

```bash
php artisan vendor:publish --tag=ocadmin-assets
```

Assets will be published to `public/vendor/ocadmin/`:

```
public/vendor/ocadmin/
├── css/
├── images/
├── js/
└── vendor/
```

### Usage in Blade Templates

Reference assets using the `ocadmin_asset()` helper:

```blade
{{-- CSS --}}
<link rel="stylesheet" href="{{ ocadmin_asset('css/stylesheet.css') }}">

{{-- JavaScript --}}
<script src="{{ ocadmin_asset('js/common.js') }}"></script>

{{-- Images --}}
<img src="{{ ocadmin_asset('images/logo.png') }}">
```

### Customizing Styles

For custom styles, place files in `public/ocadmin/` (separate from published assets):

```
public/
├── vendor/ocadmin/        ← Published assets (ignored by .gitignore)
│   ├── css/
│   ├── js/
│   └── ...
│
└── ocadmin/               ← Custom assets (committed to version control)
    └── css/
        └── custom.css
```

Load custom styles after package styles in your layout:

```blade
{{-- Package styles --}}
<link rel="stylesheet" href="{{ ocadmin_asset('css/stylesheet.css') }}">

{{-- Custom styles (override) --}}
<link rel="stylesheet" href="{{ asset('ocadmin/css/custom.css') }}">
```

### .gitignore Configuration

Published assets should not be committed to version control. Add to your project's `.gitignore`:

```gitignore
/public/vendor
```

> **Note:** `public/ocadmin/` (custom assets) should NOT be in `.gitignore` - these are your project customizations.

---

## Configuration

`config/ocadmin.php`:

```php
<?php

return [
    // Route prefix
    'prefix' => 'ocadmin',

    // Middleware
    'middleware' => ['web', 'auth', 'locale'],

    // Default locale
    'locale' => 'zh-TW',

    // Enabled standard modules
    'modules' => [
        'system-log' => true,
        'access-control' => true,
        'taxonomy' => true,
        'localization' => true,
        'setting' => true,
        'meta-key' => true,
    ],

    // Custom modules path
    'custom_modules_path' => app_path('Ocadmin/Modules'),

    // Model class configuration
    // Specify project Models or package Models
    'models' => [
        'user' => App\Models\User::class,
        // 'log' => Elonphp\LaravelOcadminModules\Models\Log::class,
        // 'setting' => Elonphp\LaravelOcadminModules\Models\Setting::class,
    ],
];
```

---

## Model Configuration

### Model Sources

| Source | Namespace | Description |
|--------|-----------|-------------|
| Project Model | `App\Models\` | Existing project Models |
| Package Model | `Elonphp\LaravelOcadminModules\Models\` | Built-in package Models |
| Custom Module Model | `App\Ocadmin\Modules\{Name}\Models\` | Custom module Models |

### Specify Project Model

When package and project have Models with same purpose (e.g., `User`), specify via config:

```php
// config/ocadmin.php
'models' => [
    'user' => App\Models\User::class,           // Use project's User
    'setting' => App\Models\Setting::class,     // Use project's Setting
],
```

### Use Package Model

If not configured, package defaults are used:

```php
// config/ocadmin.php
'models' => [
    'user' => App\Models\User::class,
    // 'log' not configured, uses package default
],
```

### Built-in Package Models

| Model | Purpose | Default Class |
|-------|---------|---------------|
| `user` | User | `App\Models\User` |
| `log` | System log | `Elonphp\...\Models\Log` |
| `setting` | System settings | `Elonphp\...\Models\Setting` |
| `role` | Role | `Spatie\...\Models\Role` |
| `permission` | Permission | `Spatie\...\Models\Permission` |

### Usage in Code

```php
use Elonphp\LaravelOcadminModules\Facades\Ocadmin;

// Get Model class
$userClass = Ocadmin::model('user');
$user = $userClass::find(1);

// Or use helper
$user = ocadmin_model('user')::find(1);
$logs = ocadmin_model('log')::latest()->get();
```

### Custom Module Models

Custom module Models are placed within the module, use full namespace directly:

```php
// app/Ocadmin/Modules/Inventory/Models/Product.php
namespace App\Ocadmin\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'pos_products';
}
```

```php
// Usage
use App\Ocadmin\Modules\Inventory\Models\Product;

$products = Product::all();
```

---

## Namespaces

| Location | Namespace | Example |
|----------|-----------|---------|
| Package core | `Elonphp\LaravelOcadminModules\Core\` | `Core\Controllers\Controller` |
| Standard modules | `Elonphp\LaravelOcadminModules\Modules\{Name}\` | `Modules\SystemLog\Controllers\LogController` |
| Custom modules | `App\Ocadmin\Modules\{Name}\` | `App\Ocadmin\Modules\Inventory\Controllers\ProductController` |

> Since `app/Ocadmin/` is under Laravel's `app/` directory, the `App\Ocadmin\` namespace is automatically handled by Laravel's PSR-4 autoload. No `composer.json` modification needed.

---

## Requirements

- PHP >= 8.2
- Laravel >= 11.0
- spatie/laravel-permission >= 6.0

---

## License

Proprietary License

---

## Changelog

### v1.0.0 (Planned)
- Initial release
- Core framework
- Standard modules
- Customization mechanism
