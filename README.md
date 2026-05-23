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

### Setup

```bash
composer run setup
```

This single command installs dependencies, generates an app key, runs migrations, and builds frontend assets.

### Running locally

```bash
composer run dev
```

Starts the Laravel dev server, queue worker, log watcher, and Vite dev server concurrently.

### Running tests

```bash
composer run test
```

## Key Features

- Interactive city grid with cell selection and function assignment
- Real-time QoL score calculation via API
- Function removal and reassignment
- User authentication and profile management
