# Metropolis

A web-based city planning simulation where users design a city by assigning functions to a grid and track the impact on Quality of Life (QoL) across multiple dimensions.

## Overview

Users place city functions — such as residential areas, parks, commercial zones, and infrastructure — onto a 3×4 city grid. Each function carries weighted scores across five categories:

- **Livability** — comfort and quality of daily life
- **Safety** — public safety and emergency services
- **Economy** — commercial activity and employment
- **Environment** — green space and ecological impact
- **Welfare** — social services and community wellbeing

The application calculates a live QoL score based on the current grid layout, giving immediate feedback on planning decisions.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Build:** Vite
- **Testing:** Pest
- **Auth:** Laravel Breeze

## Getting Started

### Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL

### Setup

**1. Install dependencies**

```bash
composer install
npm install
```

**2. Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database credentials:

```
DB_DATABASE=metropolis_groep_d
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**3. Run migrations and seed**

```bash
php artisan migrate
php artisan db:seed
```

you can also try this instead to do both and seed everytime.
```
php artisan migrate:fresh --seed
```

The seeder creates the city grid and four test accounts (see [Test Accounts](#test-accounts) below).

**4. Build frontend assets**

```bash
npm run build
```

### Running locally

#### Option A — Laravel Herd (recommended)

[Laravel Herd](https://herd.laravel.com) is a zero-config local PHP environment for Mac and Windows. If you have Herd installed and the project is inside your Herd sites directory, it's already served automatically. Just run:

```bash
npm run dev
```

Then open `http://metropolis-groep-d.test` in your browser.

#### Option B — Built-in dev server

Open two terminals and run:

```bash
# Terminal 1 — Laravel dev server
php artisan serve

# Terminal 2 — Vite dev server (hot reload)
npm run dev
```

The app will be available at `http://localhost:8000`.

### Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database — no extra configuration needed.

## Test Accounts

After running `php artisan db:seed`, the following accounts are available:

| Role | Email | Password |
|---|---|---|
| Administrator | administrator@metropolis.test | administrator@metropolis.test |
| City Planner | cityplanner@metropolis.test | cityplanner@metropolis.test |
| Expert in Effects | expert@metropolis.test | expert@metropolis.test |
| Policy Maker | policymaker@metropolis.test | policymaker@metropolis.test |

## Key Features

- Interactive city grid with cell selection and function assignment
- Real-time QoL score calculation via API
- Function removal and reassignment
- User authentication and profile management
