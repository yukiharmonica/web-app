# AI Diary - Laravel 12 + Livewire 3 + Volt Project

## Architecture Overview

**AI Diary** is a Laravel 12 application using **Livewire 3** with **Volt** (single-file components), **Breeze** for authentication, and **Tailwind CSS 3** for styling. The project uses a **monorepo structure** with separate infrastructure and application directories.

### Project Structure

```
/home/ubuntu/aiDiary/
├── infrastructure/        # Docker infrastructure (shared services)
│   ├── docker-compose.yml # Production services: app, nginx, db, redis, queue
│   ├── Dockerfile         # PHP 8.3-FPM Alpine with extensions
│   └── nginx/             # Nginx configuration
└── web-app/              # Laravel application
    ├── docker-compose.yml # Local dev services: mysql, redis, mailpit
    └── .devcontainer/     # Dev Container for VS Code
```

### Key Stack Components

- **Backend**: Laravel 12 (PHP 8.2+, deployed with PHP 8.3-FPM Alpine)
- **Frontend**: Livewire 3 + Volt, Tailwind CSS 3, Vite 7
- **Auth**: Laravel Breeze with Volt-powered auth pages
- **Database**: MySQL 8.0 (production), SQLite/MySQL (local dev)
- **Cache/Queue**: Redis 7
- **Deployment**: Docker Compose with separate infrastructure layer

## Development Workflow

### Two Deployment Modes

**Local Dev (Simple)**: Use `web-app/docker-compose.yml` for local services only:

```bash
cd web-app
docker compose up -d        # MySQL, Redis, Mailpit only
composer dev                # Runs: server, queue, pail, vite concurrently
```

**Production/Full Docker**: Use `infrastructure/docker-compose.yml` for complete containerized environment:

```bash
cd infrastructure
docker-compose up -d        # app, nginx, db, redis, queue containers
docker-compose exec app php artisan migrate
```

See [../infrastructure/.github/copilot-instructions.md](../../infrastructure/.github/copilot-instructions.md) for full Docker workflow.

### Dev Container (VS Code)

Open `web-app` in VS Code with Dev Containers extension:

- Auto-connects to `infrastructure/docker-compose.yml` → `app` service
- Workspace: `/var/www/html` (mounted from `web-app/`)
- User: `www-data` (UID 1000)
- Pre-installed extensions: Intelephense, Laravel, Blade, Pint, PHPStan, Tailwind

Configuration: [.devcontainer/devcontainer.json](.devcontainer/devcontainer.json)

### Key Commands

```bash
composer dev                # Local dev: server:8000, queue, logs, vite (HMR)
composer setup              # Initial setup: install, key:generate, migrate, build
composer test               # Run tests (clears config first)
php artisan serve           # Start server only
npm run dev                 # Vite HMR only
php artisan migrate         # Database migrations
php artisan pail            # Real-time logs
```

## Livewire + Volt Patterns

### Volt Single-File Components (SFC)

Volt components live in Blade files with inline PHP classes. **Mount paths** configured in [app/Providers/VoltServiceProvider.php](../app/Providers/VoltServiceProvider.php):

- `resources/views/livewire/**`
- `resources/views/pages/**`

#### Volt Component Structure

```php
<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.guest')] class extends Component {
    public string $property = '';

    public function mount(): void { }
    public function action(): void { }
}; ?>

<div>
    <!-- Blade template -->
</div>
```

**Examples**:

- [resources/views/livewire/pages/auth/login.blade.php](../resources/views/livewire/pages/auth/login.blade.php) - Auth page with form
- [resources/views/livewire/profile/update-profile-information-form.blade.php](../resources/views/livewire/profile/update-profile-information-form.blade.php) - Profile form component

### Livewire Class-Based Components

For reusable logic, use separate PHP classes:

- **Forms**: [app/Livewire/Forms/](../app/Livewire/Forms/) - Extend `Livewire\Form`, use `#[Validate]` attributes (see [LoginForm.php](../app/Livewire/Forms/LoginForm.php))
- **Actions**: [app/Livewire/Actions/](../app/Livewire/Actions/) - Invokable classes for single actions (see [Logout.php](../app/Livewire/Actions/Logout.php))

### Rendering Livewire Components

```blade
<livewire:profile.update-password-form />  <!-- Class-based or registered Volt -->
```

### Livewire Wire Directives

Common patterns used throughout the app:

- `wire:model="property"` - Two-way data binding
- `wire:submit="method"` - Form submission
- `wire:click="method"` - Click handler
- `wire:navigate` - SPA-style navigation (preserves state)

**Example from auth forms**: `wire:submit="login"` + `wire:model="form.email"`

## Routing Conventions

### Volt Routes

Defined in [routes/auth.php](../routes/auth.php) using `Volt::route()`:

```php
Volt::route('login', 'pages.auth.login')->name('login');
```

Maps to `resources/views/livewire/pages/auth/login.blade.php`

### Traditional Routes

[routes/web.php](../routes/web.php) uses `Route::view()` for simple views:

```php
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
```

## View Architecture

### Layouts

- `layouts/app.blade.php` - Authenticated layout
- `layouts/guest.blade.php` - Guest layout (auth pages)

### Blade Components

Reusable UI components in [resources/views/components/](../resources/views/components/):

- Form inputs: `text-input`, `input-label`, `input-error`
- Buttons: `primary-button`, `secondary-button`, `danger-button`
- Navigation: `nav-link`, `responsive-nav-link`, `dropdown`

Usage: `<x-primary-button>Save</x-primary-button>`

## Asset Pipeline (Vite)

### Configuration

- [vite.config.js](../vite.config.js) - Inputs: `resources/css/app.css`, `resources/js/app.js`
- [tailwind.config.js](../tailwind.config.js) - Scans `resources/views/**/*.blade.php` + vendor views

### Building Assets

```bash
npm run dev    # Development with HMR
npm run build  # Production build
```

### Tailwind Usage

- Forms plugin included via `@tailwindcss/forms`
- Custom font: Figtree (extended in theme)

## Testing

Run tests with:

```bash
composer test    # Recommended (clears config first)
php artisan test # Direct PHPUnit execution
```

Test structure:

- [tests/Feature/](../tests/Feature/) - Feature tests including auth flows
- [tests/Unit/](../tests/Unit/) - Unit tests

## Code Quality

### Code Style

**Laravel Pint** (PHP CS Fixer) configured for Laravel preset:

```bash
./vendor/bin/pint  # Fix code style
```

### Static Analysis

**PHPStan** with Larastan for Laravel-specific analysis:

```bash
./vendor/bin/phpstan analyse  # Run static analysis
```

Configuration in [phpstan.neon](../phpstan.neon):

- Level 5 (moderate strictness)
- Analyzes: `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `tests/`

### Pre-commit Hooks

**lint-staged** configuration in [.lintstagedrc.json](../.lintstagedrc.json):

- PHP files: Auto-format with Pint
- JS/TS files: Prettier + ESLint
- Other files: Prettier formatting

Setup with Husky (already configured):

```bash
npm install  # Installs Husky hooks automatically via "prepare" script
```

### ESLint Configuration

[eslint.config.js](../eslint.config.js) uses flat config format (ESLint 9+):

- ECMAScript latest with module support
- Prettier integration for consistent formatting
- Ignores: `node_modules`, `vendor`, `public/build`, `storage`, `bootstrap/cache`

## CI/CD

### GitHub Actions

[.github/workflows/laravel.yml](workflows/laravel.yml) runs on push/PR:

- Code style check (Pint)
- Static analysis (PHPStan)
- Test suite (PHPUnit)

## Local Development Services

### Docker Compose (Local Dev)

[docker-compose.yml](../docker-compose.yml) provides lightweight services for local development:

- **MySQL 8.0** (`:3306`) - `laravel` database
- **Redis 7** (`:6379`) - Cache and queue backend
- **Mailpit** (`:1025` SMTP, `:8025` Web UI) - Email testing

Start services:

```bash
docker compose up -d
```

Connect Laravel to these services in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

REDIS_HOST=127.0.0.1

MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

**Note**: For full containerized deployment with nginx and queue workers, use `infrastructure/docker-compose.yml` instead.

## Key Files Reference

- [composer.json](../composer.json) - Contains `dev`, `setup`, `test` scripts
- [bootstrap/app.php](../bootstrap/app.php) - Application bootstrap
- [app/Models/User.php](../app/Models/User.php) - User model
- [database/migrations/](../database/migrations/) - Database schema
