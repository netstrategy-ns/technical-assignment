# Ticketing Portal - Setup Instructions

## Prerequisites

- **PHP** 8.2+
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- **MySQL** 8.0+

## Step 1: Install Dependencies

```bash
composer install
npm install
```

## Step 2: Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your MySQL connection:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing
DB_USERNAME=root
DB_PASSWORD=
```

## Step 3: Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ticketing;"
```

## Step 4: Run Migrations and Seed Data

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- 1 test user + 10 additional users
- 6 event categories
- 12 events (featured, queue-enabled, sale-not-started, limited tickets)
- 2-3 ticket types per event

## Step 5: Start the Application

```bash
composer dev
```

This runs concurrently:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite dev server (`npm run dev`)

## Step 6: Start the Scheduler (Separate Terminal)

The scheduler handles automatic expiry of holds and queue processing. Open a **second terminal** and run:

```bash
php artisan schedule:work
```

## Step 7: Access the Application

- **URL**: http://localhost:8000
- **Test User**: `test@example.com` / `password`

## Key Features to Test

1. **Home page** (`/`) — Featured events
2. **Event listing** (`/events`) — Search, filter by category/city/date, sort
3. **Event detail** (`/events/{slug}`) — View tickets, reserve with 10-min hold
4. **Queue system** — Events marked "Queue Required" require joining a queue first
5. **Checkout** (`/events/{slug}/checkout`) — Simulated purchase from held tickets
6. **My Orders** (`/orders`) — View purchase history
7. **Sale not started** — Events with future sale dates show a notice

## Architecture Notes

- **Hold system**: 10-minute temporary reservations using `SELECT FOR UPDATE` to prevent overselling
- **Queue system**: Per-event configurable, manages concurrent access with waiting/active/expired/completed states
- **Idempotent checkout**: Uses idempotency keys to prevent double purchases
- **No Redis**: All locking, queuing, and caching uses MySQL (as required)
