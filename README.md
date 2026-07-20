# Paint Store Management System

A complete Laravel-based paint store management application with REST API, JWT authentication, Razorpay payments, and Filament admin panel.

## Tech Stack

- **Backend:** Laravel 13
- **Database:** MySQL
- **Auth:** JWT (tymon/jwt-auth)
- **Admin Panel:** Simple Blade views at `/admin` (session-based login)
- **Payments:** Razorpay

## Features

### User Roles
- **Admin** — Full management via Filament admin panel
- **Customer** — Register, browse products, cart, orders, payments, painter bookings
- **Painter** — Manage assigned painting bookings
- **Delivery Agent** — Manage assigned deliveries

### Modules
- Product catalog with categories, images, search & filters
- Shopping cart and order management
- Razorpay online payments
- Painter booking system with image uploads
- Delivery tracking with proof uploads
- Admin dashboard with statistics

## Setup

### Prerequisites
- PHP 8.3+
- Composer
- MySQL 8+
- PHP extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo

### Installation

```bash
cd /var/www/html/paint-store
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan jwt:secret
```

### Database Configuration (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paint_store
DB_USERNAME=admin
DB_PASSWORD=StrongPassword123!

RAZORPAY_KEY=your_razorpay_key
RAZORPAY_SECRET=your_razorpay_secret
```

### Run Migrations & Seeders

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### Start Server

```bash
php artisan serve
```

- **API Base URL:** `http://localhost:8000/api/v1`
- **Admin Panel:** `http://localhost:8000/admin`

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@paintstore.com | password |
| Customer | customer@paintstore.com | password |
| Painter | painter@paintstore.com | password |
| Delivery Agent | delivery@paintstore.com | password |

## Project Structure

```
app/
├── Enums/           # UserRole, OrderStatus, PaymentStatus, etc.
├── Filament/        # Admin panel resources & widgets
├── Http/
│   ├── Controllers/Api/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Repositories/
└── Services/
database/
├── migrations/
└── seeders/
docs/
└── API.md           # Full API documentation
routes/
└── api.php
```

## API Documentation

See [docs/API.md](docs/API.md) for complete endpoint reference.

### Postman Collection

Import into Postman:

| File | Purpose |
|------|---------|
| `docs/postman/Paint-Store-API.postman_collection.json` | All 44 API endpoints |
| `docs/postman/Paint-Store-Local.postman_environment.json` | Local environment variables |

**Quick start:**
1. Postman → **Import** → select both files
2. Select **Paint Store - Local** environment
3. Run **Login Customer** or **Login Staff** — JWT tokens save automatically
4. Use folders: Customer, Painter, Delivery Agent

## Architecture

- **Controllers** — Handle HTTP requests/responses
- **Services** — Business logic (orders, cart, payments, deliveries)
- **Repositories** — Data access layer
- **Policies** — Authorization rules
- **Resources** — API response transformers
- **Requests** — Input validation

## License

MIT
