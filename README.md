# HRM - Human Resource Management System

A basic example project demonstrating how to build a Human Resource Management system with Laravel 12. This project showcases multi-portal architecture, role-based access control, and multi-language support as a reference implementation.

> **Note**: This is a foundational example project for learning and reference purposes, not a production-ready HRM solution.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade + jQuery + Bootstrap 5
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission (wildcard permissions)
- **i18n**: Multi-language with URL-based locale switching
- **Database**: MySQL / MariaDB / SQLite

## Features

### Multi-Portal Architecture

The system uses a portal-based architecture under `app/Portals/`, allowing multiple independent entry points (e.g., Ocadmin for back-office management, ESS for employee self-service).

### Ocadmin Portal (Back-Office)

- **Dashboard** - Overview and quick navigation
- **Access Control** - Permission, role, and user management with granular RBAC
- **Organization Management** - Company/organization CRUD with translations
- **Taxonomy & Term** - Configurable classification system (hierarchical vocabularies)
- **System Settings** - Application-wide configuration

### Core Capabilities

- **Modular design** - Core features vs pluggable Modules under `Portals/Ocadmin/Modules/`
- **Multi-language** - Translation support on models via `HasTranslation` trait
- **AJAX-driven UI** - List filtering, sorting, pagination, batch operations
- **Breadcrumb navigation** - Automatic breadcrumb generation per controller

## Project Structure

```
app/
├── Models/                          # Eloquent models
├── Portals/
│   └── Ocadmin/
│       ├── Core/                    # Core controllers, views, providers
│       │   ├── Controllers/
│       │   │   ├── Acl/             # Permission, Role, User
│       │   │   ├── Config/          # Taxonomy, Term
│       │   │   └── System/          # Setting
│       │   ├── Views/
│       │   ├── Providers/
│       │   ├── ViewComposers/
│       │   └── Middleware/
│       ├── Modules/                 # Pluggable feature modules
│       │   ├── Dashboard/
│       │   └── Organization/
│       └── routes/
├── Traits/                          # Shared traits (HasTranslation, etc.)
database/
├── migrations/
└── seeders/
lang/
└── zh_Hant/                         # Traditional Chinese
```

## Getting Started

### Requirements

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL / MariaDB (or SQLite for development)

### Installation

```bash
git clone <repository-url>
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### Development

```bash
composer dev
```

This starts the Laravel dev server, queue worker, log viewer, and Vite in parallel.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
