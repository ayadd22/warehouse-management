# Warehouse Management System

A **pure PHP** warehouse management system built without any framework.

## Overview

| Aspect | Detail |
|---|---|
| Language | PHP 8.1+ |
| Dependency manager | Composer |
| Autoloading | PSR-4 (`App\` → `src/`) |
| Database | MySQL (PDO, added in a later task) |
| Framework | None |

The system tracks products, suppliers, customers, and stock movements
(supplies in / sales out) backed by the relational schema in `sql/database.sql`.

## Installation

```bash
# 1. Clone the repository
git clone <repo-url>
cd warehouse-management

# 2. Install Composer dependencies
composer install
```

## Project Structure

```
warehouse-management/
│
├── src/                   # Application source (PSR-4 namespace: App\)
│   ├── Contracts/         # Interfaces / contracts
│   ├── Database/          # Database infrastructure (connection, etc.)
│   ├── Enums/             # PHP enumerations (e.g. MovementType)
│   ├── Exceptions/        # Custom exception classes
│   ├── Models/            # Domain models / entities
│   ├── Repositories/      # Data-access layer (implemented later)
│   ├── Services/          # Business logic services (implemented later)
│   └── Traits/            # Reusable traits
│
├── public/
│   └── index.php          # Public entry point
│
├── sql/
│   └── database.sql       # MySQL schema
│
├── composer.json
├── composer.lock
├── .gitignore
└── README.md
```

## Namespace Convention (PSR-4)

| File | Namespace |
|---|---|
| `src/Models/Product.php` | `App\Models` |
| `src/Services/StockService.php` | `App\Services` |
| `src/Repositories/ProductRepository.php` | `App\Repositories` |
| `src/Enums/MovementType.php` | `App\Enums` |

> Models, repositories, services, and PDO wiring will be added in subsequent tasks.
