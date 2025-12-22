# laravel-ocadmin

A Laravel admin panel system using OpenCart's backend frontend.

## Key Features

- Built on Laravel 12 framework
- Frontend adopts OpenCart 4 admin styles
- Controller design inspired by OpenCart admin architecture
- Supports EAV (Entity-Attribute-Value) pattern for flexible field extension
- Built-in system logging

## Tech Stack

- **Backend**: PHP 8.2+ / Laravel 12
- **Database**: MariaDB / MySQL
- **Frontend**: OpenCart Admin styles / Bootstrap 5

## Installation

```bash
# Clone the project
git clone https://github.com/your-username/laravel-ocadmin.git
cd laravel-ocadmin

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database migration
php artisan migrate

# Start development server
php artisan serve
```

## Project Structure

```
laravel-ocadmin/
├── app/
│   ├── Models/
│   │   └── Identity/               # User / Identity related Models
│   │
│   ├── Portals/
│   │   └── Ocadmin/                # Admin portal entry point
│   │       ├── Core/               # Core components (non-business modules)
│   │       │   ├── Controllers/
│   │       │   ├── Providers/
│   │       │   ├── ViewComposers/
│   │       │   └── Views/           # Shared views (layouts, auth)
│   │       │
│   │       ├── Modules/             # Backend feature modules
│   │       │   ├── Dashboard/       # Dashboard module (standalone)
│   │       │   │
│   │       │   ├── Common/          # Common base modules (category layer)
│   │       │   │   ├── Taxonomy/    # Taxonomy / Tag system module
│   │       │   │   │   ├── TaxonomyController.php
│   │       │   │   │   ├── TaxonomyService.php
│   │       │   │   │   └── Views/
│   │       │   │   │       ├── index.blade.php
│   │       │   │   │       ├── list.blade.php
│   │       │   │   │       └── form.blade.php
│   │       │   │   │
│   │       │   │   └── Term/        # Term / Vocabulary management module
│   │       │   │       ├── TermController.php
│   │       │   │       ├── TermService.php
│   │       │   │       └── Views/
│   │       │   │           ├── index.blade.php
│   │       │   │           ├── list.blade.php
│   │       │   │           └── form.blade.php
│   │       │   │
│   │       │   ├── Member/          # Member management module
│   │       │   │   ├── MemberController.php
│   │       │   │   ├── MemberService.php
│   │       │   │   └── Views/
│   │       │   │       ├── index.blade.php
│   │       │   │       ├── list.blade.php
│   │       │   │       └── form.blade.php
│   │       │   │
│   │       │   └── System/          # System modules (platform settings)
│   │       │       └── Setting/     # System settings module
│   │       │
│   │       └── routes/              # Ocadmin dedicated routes
│   │
│   ├── Repositories/                # Repository layer (data access abstraction)
│   │
│   └── Traits/
│       └── HasMetas.php              # EAV extension field Trait
│
├── public/
│   └── assets/ocadmin/              # OpenCart admin frontend static assets
│
└── docs/
    └── md/                          # Project documentation
```

## Feature Modules

- **Account Management** - User CRUD
- **System Management**
  - Vocabulary management
  - Localization settings (Countries, Divisions)
  - Parameter settings
  - Field definitions (Meta Keys)
  - System logs

## EAV Pattern

This project uses the EAV (Entity-Attribute-Value) pattern for flexible fields:

```php
// Transparent access via HasMetas trait
$user->phone = '0912345678';
$user->save();

// Or explicit operations
$user->setMeta('birthday', '1990-01-01');
$user->getMeta('phone');
```

For detailed documentation, refer to the `Ocadmin/Docs/` directory.

## License

This project is licensed under the [MIT License](LICENSE).
