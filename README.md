# Warehouse Management System

A pure PHP warehouse management system built without any framework.

## Project Purpose

The system manages a wholesale warehouse that buys construction materials from suppliers and sells them to customers.

It replaces informal record-keeping such as Excel spreadsheets with a normalized relational database and a clean layered PHP architecture.

## Original Warehouse Problem

The warehouse previously relied on informal records, which caused several problems:

* Duplicate or inconsistent supplier records due to different phone-number formats.
* No reliable stock level because quantities were updated manually.
* Risk of overselling because stock was not derived from actual supply and sale transactions.
* Historical prices were not clearly separated from transaction records.
* No reliable referential integrity between products, suppliers, customers, and stock movements.

## Main Entities

| Entity            | Description                                                                    |
| ----------------- | ------------------------------------------------------------------------------ |
| **Product**       | Canonical product catalogue containing name, unit of measure, and description. |
| **Supplier**      | Supplier master record containing name, normalized phone, and address.         |
| **Customer**      | Customer/trader master record containing name, phone, and address.             |
| **StockMovement** | Immutable ledger containing every supply-in and sale-out transaction.          |

### Product-Supplier Relationship

A separate `product_suppliers` table is **not used** in the current design.

The supplier-product relationship is represented through `stock_movements`:

* Every supply movement identifies the supplied product.
* Every supply movement identifies the supplier.
* The purchase price at that specific time is stored in `stock_movements.unit_price`.
* The same product can therefore be supplied by different suppliers at different prices.
* Historical supplier prices remain attached to the actual supply transaction.

This design avoids storing duplicate or redundant supplier-product information when the system only needs the supplier and price associated with each actual supply event.

---

## Database Normalization

The schema in `sql/database.sql` follows normalization principles up to **Third Normal Form (3NF)**.

### 1NF

* Every column contains a single atomic value.
* There are no repeating groups or arrays stored inside columns.
* Each table has a primary key.

### 2NF

* Non-key attributes depend on the complete primary key.
* The current schema uses single-column primary keys for the main entities.
* There is no unnecessary composite relationship table in the current design.

### 3NF

* Non-key attributes do not depend on other non-key attributes.
* Supplier information is stored in `suppliers`.
* Customer information is stored in `customers`.
* Product information is stored in `products`.
* Stock transaction information is stored in `stock_movements`.
* Supplier and customer names are not duplicated inside stock movements.
* `unit_price` in `stock_movements` represents the historical price of that specific transaction.

---

## Architecture

The project follows a layered architecture:

```text
public/index.php
        │
        │ Bootstrap / Entry Point
        ▼
config/database.php
        │
        ▼
App\Database\DatabaseConnection
        │
        │ creates PDO
        ▼
      PDO
        │
        ▼
┌─────────────────────────────────────┐
│           Service Layer             │
│                                     │
│ ProductService                      │
│ SupplierService                     │
│ CustomerService                     │
│ StockMovementService                │
│                                     │
│ Business rules are handled here.    │
└────────────────┬────────────────────┘
                 │
                 │ depends on interfaces
                 ▼
┌─────────────────────────────────────┐
│       Repository Interfaces         │
│                                     │
│ ProductRepositoryInterface          │
│ SupplierRepositoryInterface         │
│ CustomerRepositoryInterface         │
│ StockMovementRepositoryInterface    │
└────────────────┬────────────────────┘
                 │
                 │ implemented by
                 ▼
┌─────────────────────────────────────┐
│       Concrete Repositories         │
│                                     │
│ ProductRepository                   │
│ SupplierRepository                  │
│ CustomerRepository                  │
│ StockMovementRepository             │
│                                     │
│ SQL and PDO access live here.       │
└────────────────┬────────────────────┘
                 │
                 ▼
              MySQL
```

### Layer Responsibilities

**Models**

Represent domain data and contain no SQL.

**Services**

Contain business rules and coordinate repositories.

**Repositories**

Contain SQL queries, PDO operations, and database-to-model mapping.

**DatabaseConnection**

Creates and configures the PDO connection.

**Entry Point**

Bootstraps the application and handles top-level execution.

---

## Repository Pattern

The project uses the **Repository Pattern** as its single design pattern.

### Why Repository Pattern?

The Repository Pattern isolates database access behind interfaces.

Services depend on repository interfaces rather than concrete database implementations.

This provides:

* Separation between business logic and SQL.
* Centralized PDO/database access.
* Easier testing of business logic.
* Reduced coupling between services and the database implementation.
* The possibility of changing the data-access implementation without rewriting the services.

SQL is kept inside repository classes and is not placed inside models or services.

---

## Stock Calculation

Current stock is **not stored** as a mutable quantity column on the `products` table.

Instead, stock is calculated from the movement history.

```text
Current Stock =
Total Supply Quantity - Total Sale Quantity
```

The calculation is implemented by:

```text
StockMovementRepository::getCurrentStock()
```

Conceptually:

```sql
SUM(supply quantities) - SUM(sale quantities)
```

This means the current stock is derived from the actual transaction history rather than from a separately maintained quantity field.

---

## Stock Movement Rules

Each stock movement has:

* Product
* Movement type
* Quantity
* Unit price
* Supplier or customer
* Creation timestamp

The movement type is represented by the PHP enum:

```php
MovementType::Supply
MovementType::Sale
```

### Supply

A supply movement:

* Must reference an existing product.
* Must reference an existing supplier.
* Must have a quantity greater than zero.
* Must have a valid non-negative unit price.
* Has `customer_id = NULL`.
* Increases the calculated stock.

### Sale

A sale movement:

* Must reference an existing product.
* Must reference an existing customer.
* Must have a quantity greater than zero.
* Must have a valid non-negative unit price.
* Has `supplier_id = NULL`.
* Must not exceed the available stock.
* Decreases the calculated stock.

---

## Sale and Overselling Protection

Before creating a sale, `StockMovementService::sell()` calculates the current stock.

If:

```text
requested quantity > available stock
```

the service throws:

```text
InsufficientStockException
```

and no sale movement is created.

Therefore, a normal sale cannot make the calculated stock negative.

---

## Supplier Duplication Prevention

The original warehouse data contained supplier records with inconsistent phone-number formatting.

For example:

```text
0933-123-456
0933 123 456
(0933) 123 456
```

The system normalizes supplier phone numbers before storing and comparing them.

`SupplierService::normalizePhone()` removes non-digit characters.

For example:

```text
0933-123-456
        ↓
0933123456
```

The normalized value is stored in the database.

Supplier creation also checks for an existing supplier with the same:

```text
name + normalized phone
```

The database additionally protects against duplicates through:

```sql
UNIQUE KEY uq_suppliers_name_phone (name, phone)
```

Two suppliers with the same name but different phone numbers are allowed.

---

## Monetary Value Representation

`unit_price` is stored in MySQL as:

```sql
DECIMAL(12,2)
```

In PHP, monetary values are represented as strings such as:

```php
'150.00'
```

This avoids using floating-point values for monetary representation.

Prices are validated before a stock movement is created.

The accepted format allows:

* Non-negative values.
* Up to 10 digits before the decimal point.
* Up to 2 digits after the decimal point.

Example:

```text
150
150.00
1250.50
```

---

## Exception Handling

The application uses exceptions for invalid operations instead of `die()` or `echo` inside business or database layers.

| Exception                       | Purpose                                                                     |
| ------------------------------- | --------------------------------------------------------------------------- |
| `InsufficientStockException`    | Thrown when a sale exceeds available stock.                                 |
| `InvalidStockMovementException` | Thrown when a stock movement violates a business rule.                      |
| `RuntimeException`              | Used for not-found entities, duplicate records, and other runtime failures. |
| `InvalidArgumentException`      | Used where invalid arguments are rejected by domain objects.                |

### Error Handling Rules

* Repositories do not print errors.
* Services do not print errors.
* Business failures are represented by exceptions.
* The entry point handles top-level exceptions.
* Database credentials are not printed as part of normal error handling.

---

## Composer and PSR-4

The project uses Composer for PSR-4 autoloading.

Relevant configuration:

```json
{
    "require": {
        "php": ">=8.1"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Install dependencies:

```bash
composer install
```

Regenerate the autoloader after adding or changing classes:

```bash
composer dump-autoload
```

---

## PSR-4 Namespace Map

| Directory           | Namespace          |
| ------------------- | ------------------ |
| `src/Models/`       | `App\Models`       |
| `src/Contracts/`    | `App\Contracts`    |
| `src/Repositories/` | `App\Repositories` |
| `src/Services/`     | `App\Services`     |
| `src/Database/`     | `App\Database`     |
| `src/Enums/`        | `App\Enums`        |
| `src/Exceptions/`   | `App\Exceptions`   |
| `src/Traits/`       | `App\Traits`       |

---

## Database Configuration

The application reads database configuration from environment variables.

Example:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wms
DB_USERNAME=root
DB_PASSWORD=
```

PHP does not automatically load `.env` files.

Environment variables must therefore be configured in the shell or web-server environment.

### PowerShell

```powershell
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='3306'
$env:DB_DATABASE='wms'
$env:DB_USERNAME='root'
$env:DB_PASSWORD='your_password'
```

The project also includes `.env.example` as a configuration reference.

---

## How to Run the Project

### 1. Create the Database

Create the MySQL database:

```sql
CREATE DATABASE wms
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 2. Import the Schema

Import:

```text
sql/database.sql
```

For example:

```bash
mysql -u root -p wms < sql/database.sql
```

### 3. Configure Database Variables

Set the required `DB_*` environment variables.

### 4. Install Composer Dependencies

```bash
composer install
```

### 5. Verify the Database Connection

Run:

```bash
php public/index.php
```

Expected output:

```text
Warehouse Management System — database connection OK.
PDO driver : mysql
```

---

## Testing

The project was manually integration-tested against the real MySQL database.

The test covered:

* Database connection.
* Repository creation.
* Service creation.
* Product CRUD operations.
* Supplier CRUD operations.
* Customer CRUD operations.
* Supplier phone normalization.
* Supply movement creation.
* Sale movement creation.
* Stock calculation.
* Movement retrieval.
* Overselling protection.
* Duplicate product protection.
* Duplicate supplier protection.
* Invalid quantity protection.
* Invalid price protection.
* Entity deletion.

The temporary `manual_test.php` file was used during development and was removed after the integration tests passed.

---

## Project Structure

```text
warehouse-management/
│
├── config/
│   └── database.php
│
├── public/
│   └── index.php
│
├── sql/
│   └── database.sql
│
├── src/
│   ├── Contracts/
│   │   └── Repository interfaces
│   │
│   ├── Database/
│   │   └── DatabaseConnection.php
│   │
│   ├── Enums/
│   │   └── MovementType.php
│   │
│   ├── Exceptions/
│   │   ├── InsufficientStockException.php
│   │   └── InvalidStockMovementException.php
│   │
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── StockMovement.php
│   │   └── Supplier.php
│   │
│   ├── Repositories/
│   │   ├── CustomerRepository.php
│   │   ├── ProductRepository.php
│   │   ├── StockMovementRepository.php
│   │   └── SupplierRepository.php
│   │
│   ├── Services/
│   │   ├── CustomerService.php
│   │   ├── ProductService.php
│   │   ├── StockMovementService.php
│   │   └── SupplierService.php
│   │
│   └── Traits/
│       └── HasId.php
│
├── .env.example
├── composer.json
├── composer.lock
├── .gitignore
└── README.md
```

---

## Design Principles

The implementation follows these principles:

* Pure PHP without a framework.
* PDO for database access.
* PSR-4 autoloading through Composer.
* Separation of business logic and data access.
* Repository Pattern.
* Dependency Injection.
* Interfaces for repository contracts.
* Enum for stock movement types.
* Trait for shared entity ID behavior.
* Exceptions for business and runtime errors.
* Normalized relational database design.
* Stock derived from immutable movement records.
* No unnecessary framework or architectural complexity.

## Requirements

* PHP 8.1 or higher
* MySQL 8.0 or compatible MySQL version
* Composer
* PDO MySQL extension
