# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Essential Commands

### Development
```bash
# Start development environment with all services
composer dev  # Runs server, queue, logs, and vite concurrently

# Individual services
php artisan serve        # Laravel development server
php artisan queue:listen # Queue worker
php artisan pail         # Real-time log monitoring
npm run dev             # Vite asset compilation
npm run build           # Production build
```

### Testing & Quality
```bash
# Run tests
php artisan test
composer test           # Alias for test command

# Code quality
php artisan pint        # Laravel Pint code formatting
```

### Database
```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Generate migrations
php artisan make:migration create_table_name

# Generate models with all options
php artisan make:model ModelName -mfsc
```

### Queue & Jobs
```bash
# Queue management
php artisan queue:work
php artisan queue:listen --tries=1
php artisan queue:restart

# Generate jobs
php artisan make:job JobName
```

## Application Architecture

### Role-Based Multi-Tenant System
The application is a delivery management system with four distinct user roles:
- **CLIENT**: Create and track packages, manage wallet
- **DELIVERER**: Accept and deliver packages, scan QR codes
- **COMMERCIAL**: Process topup requests, handle complaints
- **SUPERVISOR**: Full system access and oversight

### Route Organization
Routes are organized by role in separate files:
- `routes/web.php` - Main routing with role-based redirects
- `routes/client.php` - Client-specific routes
- `routes/deliverer.php` - Deliverer-specific routes
- `routes/commercial.php` - Commercial-specific routes
- `routes/supervisor.php` - Supervisor-specific routes
- `routes/auth.php` - Authentication routes

### Key Models & Relationships
- **User**: Central model with role-based relationships to packages, wallets, complaints
- **Package**: Core entity with status tracking, COD management, delegation assignment
- **UserWallet**: Financial management for clients and deliverers
- **RunSheet**: Batch delivery management for deliverers
- **TopupRequest**: Wallet recharge system with bank/cash methods
- **Complaint**: Customer service system

### Middleware & Security
- Custom role middleware: `role:CLIENT`, `role:DELIVERER`, `role:COMMERCIAL`, `role:SUPERVISOR`
- Combined roles: `role:COMMERCIAL,SUPERVISOR`
- Account status verification (ACTIVE, PENDING, SUSPENDED)
- Automatic wallet creation for clients and deliverers

### Configuration
- `config/deliverer.php`: Deliverer-specific settings (scanner, wallet thresholds, PWA)
- `config/pwa.php`: Progressive Web App configuration
- Environment-specific development routes with system info endpoints

### Package Status Flow
Package lifecycle: CREATED → AVAILABLE → ACCEPTED → PICKED_UP → DELIVERED → PAID
Alternative paths: RETURNED, REFUSED, CANCELLED

### Financial System
- Wallet-based COD management
- Automatic transaction backups
- Deliverer wallet emptying requests
- Client topup requests (bank transfer/cash)
- Commercial processing of financial operations

### PWA Features
- Offline mode support for deliverers
- QR code scanning capabilities
- Push notifications
- Service worker for caching

### Import System
- CSV batch import for packages
- ImportBatch model tracks bulk operations
- Error handling and validation for bulk data

## Development Notes

### Database
- Single comprehensive migration: `2025_09_13_161101_create_full_data_base.php`
- Recent addition: RunSheet table migration
- Uses SQLite for testing, configurable for production

### Asset Pipeline
- Vite for modern asset compilation
- Tailwind CSS for styling
- Alpine.js for frontend interactivity
- Concurrently runs all development services

### Key Packages
- **spatie/laravel-permission**: Role and permission management
- **intervention/image**: Image processing
- **maatwebsite/excel**: Excel/CSV operations
- **barryvdh/laravel-dompdf**: PDF generation
- **laravel/sanctum**: API authentication