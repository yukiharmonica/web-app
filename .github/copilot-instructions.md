# Laravel 12 + Livewire 3 + Volt Project

## Architecture Overview

This is a **Laravel 12** application using **Livewire 3** with **Volt** (single-file components), **Breeze** for authentication, and **Tailwind CSS 3** for styling.

### Key Stack Components

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 3 + Volt, Tailwind CSS 3, Vite 7
- **Auth**: Laravel Breeze with Volt-powered auth pages
- **Database**: Configured via `config/database.php` (typically SQLite/MySQL)

## Development Workflow

### Start Development Environment

```bash
composer dev  # Runs server, queue, logs (pail), and vite concurrently
```

This single command starts:

- PHP dev server (`:8000`)
- Queue listener
- Laravel Pail (real-time logs)
- Vite dev server (HMR)

### Alternative Individual Commands

```bash
php artisan serve           # Start server only
npm run dev                # Vite dev server only
php artisan test           # Run PHPUnit tests
composer test              # Clear config + run tests
```

### Initial Setup

```bash
composer setup  # Install deps, generate key, migrate, build assets
```

## Livewire + Volt Patterns

### Volt Single-File Components (SFC)

Volt components live in Blade files with inline PHP classes. **Mount paths** configured in [app/Providers/VoltServiceProvider.php](app/Providers/VoltServiceProvider.php):

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

- [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php) - Auth page with form
- [resources/views/livewire/profile/update-profile-information-form.blade.php](resources/views/livewire/profile/update-profile-information-form.blade.php) - Profile form component

### Livewire Class-Based Components

For reusable logic, use separate PHP classes:

- **Forms**: [app/Livewire/Forms/](app/Livewire/Forms/) - Extend `Livewire\Form`, use `#[Validate]` attributes (see [LoginForm.php](app/Livewire/Forms/LoginForm.php))
- **Actions**: [app/Livewire/Actions/](app/Livewire/Actions/) - Invokable classes for single actions (see [Logout.php](app/Livewire/Actions/Logout.php))

### Rendering Livewire Components

```blade
<livewire:profile.update-password-form />  <!-- Class-based or registered Volt -->
```

## Routing Conventions

### Volt Routes

Defined in [routes/auth.php](routes/auth.php) using `Volt::route()`:

```php
Volt::route('login', 'pages.auth.login')->name('login');
```

Maps to `resources/views/livewire/pages/auth/login.blade.php`

### Traditional Routes

[routes/web.php](routes/web.php) uses `Route::view()` for simple views:

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

Reusable UI components in [resources/views/components/](resources/views/components/):

- Form inputs: `text-input`, `input-label`, `input-error`
- Buttons: `primary-button`, `secondary-button`, `danger-button`
- Navigation: `nav-link`, `responsive-nav-link`, `dropdown`

Usage: `<x-primary-button>Save</x-primary-button>`

## Asset Pipeline (Vite)

### Configuration

- [vite.config.js](vite.config.js) - Inputs: `resources/css/app.css`, `resources/js/app.js`
- [tailwind.config.js](tailwind.config.js) - Scans `resources/views/**/*.blade.php` + vendor views

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

- [tests/Feature/](tests/Feature/) - Feature tests including auth flows
- [tests/Unit/](tests/Unit/) - Unit tests

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

Configuration in [phpstan.neon](phpstan.neon):

- Level 5 (moderate strictness)
- Analyzes: `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `tests/`

### Pre-commit Hooks

**lint-staged** configuration in [.lintstagedrc.json](.lintstagedrc.json):

- PHP files: Auto-format with Pint + PHPStan analysis
- JS/TS files: Prettier + ESLint
- Other files: Prettier formatting

Setup with Husky:

```bash
npm install --save-dev husky lint-staged
npx husky init
echo "npx lint-staged" > .husky/pre-commit
```

### Pre-commit Hooks

**lint-staged** configuration in [.lintstagedrc.json](.lintstagedrc.json):

- PHP files: Auto-format with Pint + PHPStan analysis
- JS/TS files: Prettier + ESLint
- Other files: Prettier formatting

Setup with Husky:

```bash
npm install --save-dev husky lint-staged
npx husky init
echo "npx lint-staged" > .husky/pre-commit
```

## CI/CD

### GitHub Actions

[.github/workflows/laravel.yml](.github/workflows/laravel.yml) runs on push/PR:

- Code style check (Pint)
- Static analysis (PHPStan)
- Test suite (PHPUnit)

## Local Development Services

### Docker Compose

[docker-compose.yml](docker-compose.yml) provides:

- MySQL 8.0 (`:3306`)
- Redis (`:6379`)
- Mailpit for email testing (`:1025` SMTP, `:8025` Web UI)

Start services:

```bash
docker compose up -d
```

Update `.env` to use these services:

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

## Key Files Reference

- [composer.json](composer.json) - Contains `dev`, `setup`, `test` scripts
- [bootstrap/app.php](bootstrap/app.php) - Application bootstrap
- [app/Models/User.php](app/Models/User.php) - User model
- [database/migrations/](database/migrations/) - Database schema
