# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This App Does

**Inspektor** is a Laravel 12 web app for crawling websites, processing sitemaps, and extracting page content at scale. Core flow: add a website → fetch sitemaps → extract pages → run content extraction jobs → view/search results.

## Commands

### Development

```bash
composer run setup        # First-time setup: install deps, generate .env & key, migrate, npm install & build
composer run dev          # Start all dev services concurrently: artisan serve, queue:listen, pail, Horizon, npm dev
npm run dev               # Vite dev server only (hot reload)
npm run build             # Production frontend build
```

### Testing & Linting

```bash
php artisan test                    # Run Pest test suite
php artisan test --filter TestName  # Run a single test
vendor/bin/pint                     # Auto-fix PHP code style
vendor/bin/pint --test              # Check style without fixing
```

### Database

```bash
php artisan migrate          # Run pending migrations
php artisan migrate:fresh    # Drop all tables and re-migrate (dev only)
php artisan db:seed          # Seed test data
```

### Docker (local full-stack)

```bash
docker compose up -d         # Start: PostgreSQL 16, Redis, Meilisearch, pgAdmin, RedisInsight
docker compose down          # Stop services
```

## Architecture

### Backend Stack

- **Laravel 12** with PHP — standard MVC, but business logic lives in Actions and Services, not controllers
- **Livewire** (+ Flux component library) for server-rendered interactive UI
- **React 19 + ReactFlow** for complex client-side visualizations (page hierarchy graph)
- **PostgreSQL** (production / Docker) or **SQLite** (default `.env`)
- **Redis** — queues, cache, session, and partial event sourcing
- **Meilisearch** — full-text search via Laravel Scout
- **Laravel Horizon** — queue monitoring dashboard at `/horizon`

### Key Patterns

**Actions** (`app/Actions/`) — thin, single-purpose classes that encapsulate all business logic for a use case. Controllers and Livewire components delegate to Actions, never hold logic themselves.

**Domain layer** (`app/Domain/ContentExtraction/`) — the content extraction system is isolated in its own namespace with its own Actions, Enums, Jobs, Models, and Services. New complex features should follow this pattern.

**Jobs + Queue** — all heavy work (metadata fetching, sitemap parsing, content extraction) is async. `StartContentExtractionRun` dispatches `ExtractPageContentJob` instances in parallel per page.

**Event store** — `ExtractionEventStore` appends events to Redis lists (TTL 1h) and streams them via SSE to the UI. This is not a full event-sourcing system — it's a lightweight real-time pub/sub for the extraction UI.

**State machine** — `ContentExtractionRunStatus` enum controls valid states (pending → running → paused/completed/failed). Check `isTerminal()`, `isInterruptible()` before state transitions.

**ULIDs** — all models use ULIDs as primary keys (not auto-increment integers). Use `Str::ulid()` for new models.

### Models

- `Website` → has many `Sitemap`, `Page`, `ContentExtractionRun`
- `ContentExtractionRun` → has many `PageExtraction` → each links to `PageContent`
- `PageExtraction` tracks per-page status; `PageContent` stores the extracted text

### Livewire + React Hybrid

Livewire handles most of the UI (listings, forms, status cards). The pages hierarchy visualization (`resources/js/pages/PagesFlow.jsx`) uses React + ReactFlow, mounted by passing JSON via `data-*` attributes on the Blade template and listening to `livewire:navigate` events for SPA transitions.

### Authentication

Laravel Fortify handles auth UI (login, register, password reset, 2FA). Sanctum handles API token auth. Both are configured in `config/fortify.php` and `config/sanctum.php`.

## Infrastructure Notes

- **Production forces HTTPS** — set in `AppServiceProvider` via `URL::forceScheme('https')`
- **Docker multi-stage build** — `Dockerfile` has three stages: base deps, composer install, final image with Nginx + PHP-FPM + Supervisor
- **CI** — GitHub Actions builds and pushes to Docker Hub on push to `main`/`master` and on semver tags. Test job exists in the workflow but is commented out.
- **Horizon** is the recommended queue driver for production (Redis-backed); the dev `composer run dev` script also starts it locally.
