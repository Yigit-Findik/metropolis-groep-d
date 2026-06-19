# Metropolis

A web-based city planning simulation where users design a city by assigning functions to a grid and track the impact on Quality of Life (QoL) across multiple dimensions.

## Overview

Users place city functions (such as residential areas, parks, commercial zones, and infrastructure) onto a 3×4 city grid. Each function carries weighted scores across five categories:

- **Livability:** comfort and quality of daily life
- **Safety:** public safety and emergency services
- **Economy:** commercial activity and employment
- **Environment:** green space and ecological impact
- **Welfare:** social services and community wellbeing

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

You can also use the following to drop all tables, re-run migrations, and seed in one go (useful when you want a clean slate):

```bash
php artisan migrate:fresh --seed
```

The seeder creates the city grid and four test accounts (see [Test Accounts](#test-accounts) below).

**4. Build frontend assets**

```bash
npm run build
```

### Running locally

#### Option A: Laravel Herd (recommended)

[Laravel Herd](https://herd.laravel.com) is a zero-config local PHP environment for Mac and Windows. If you have Herd installed and the project is inside your Herd sites directory, it's already served automatically. Just run:

```bash
npm run dev
```

Then open `http://metropolis-groep-d.test` in your browser.

#### Option B: Built-in dev server

Open two terminals and run:

```bash
# Terminal 1: Laravel dev server
php artisan serve

# Terminal 2: Vite dev server (hot reload)
npm run dev
```

The app will be available at `http://localhost:8000`.

### Email notifications (Mailtrap)

When an administrator adds a new city function, the app automatically sends an email to every user with the "Expert in Effects" role. This is handled via a queued job, so you need both a mail service and a running queue worker for it to work locally.

**1. Configure your mail service in `.env`**

The easiest option for local testing is [Mailtrap](https://mailtrap.io). Sign up, open your sandbox, and go to "SMTP Settings". You will find your credentials there. Copy them into your `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@metropolis.test"
MAIL_FROM_NAME="Metropolis"
```

Any other SMTP service (Gmail, Mailgun, Resend, etc.) works too. Just replace the values above with the credentials from that provider.

**2. Run the queue worker**

The email is dispatched via the queue, so you need the queue worker running alongside the dev server:

```bash
php artisan queue:listen
```

Once both are running, creating a new city function as an Administrator will send an email to all Effects Experts, visible in your Mailtrap inbox.

> If you just want to skip email entirely during development, set `MAIL_MAILER=log` in `.env`. Emails will be written to `storage/logs/laravel.log` instead of being sent.

### Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database, no extra configuration needed.

## User Roles

The application uses role-based access control with four roles:

| Role | What they can do |
|---|---|
| **Administrator** | Full access: manages city functions, users, effects, events, access roads, and can view the audit log |
| **City Planner** | Places and removes functions on the grid, manages events and access roads, edits effects |
| **Expert in Effects** | Views and updates the effects table, manages pending effect actions |
| **Policy Maker** | Views the grid and QoL scores, approves or revokes cell assignments, posts suggestions and comments |

## Test Accounts

After running `php artisan db:seed`, the following accounts are available:

| Role | Email | Password |
|---|---|---|
| Administrator | administrator@metropolis.test | administrator@metropolis.test |
| City Planner | cityplanner@metropolis.test | cityplanner@metropolis.test |
| Expert in Effects | expert@metropolis.test | expert@metropolis.test |
| Policy Maker | policymaker@metropolis.test | policymaker@metropolis.test |

## Team

- Yigit Findik
- Jurre van Cuijk
- Salman Mahamed
- Volodymyr Dolhov
- Ichlaas Nabibaks
- Lucas Hammers

## Key Features

**City Grid & Planning**
- Interactive 3×4 city grid with cell selection, function assignment, and removal
- Adjacency rules that restrict which cells are valid for placement
- Undo the last placed function
- Real-time QoL score calculation across five categories (Livability, Safety, Economy, Environment, Welfare)

**Approval Workflow**
- Policy makers can approve or revoke individual cell assignments
- Bulk approve or revoke all cells at once
- City planners and administrators can accept or reject improvement suggestions submitted by policy makers

**Effects & Simulation**
- Effects table where city planners and effects experts can tune category weights per function
- Pending actions queue for effect changes that require review
- City events that temporarily adjust QoL scores when activated (including a day/night cycle event)
- Access roads that can be toggled active or inactive
- Event routes linking access roads to event locations

**Collaboration & Review**
- Policy makers can post improvement suggestions on specific grid cells
- Simulation comments for general notes on the current grid state
- PDF report export, preview in browser or download directly

**Administration**
- Full audit log of all actions taken in the application
- City functions management (create, edit, soft-delete)
- Role-based access control across all features
