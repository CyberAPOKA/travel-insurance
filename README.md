# Travel Insurance Quote System

A full-stack technical assessment project for calculating travel insurance quotes. The backend exposes a pricing API built with Laravel, and the frontend provides a quote form built with Next.js.

## Requirements

- Docker and Docker Compose (recommended)
- Or locally: PHP 8.2+, Composer, Node.js 20+, npm

## Quick Start with Docker

Run the entire stack (backend, frontend, MySQL, Redis, and phpMyAdmin) with one command:

```bash
docker compose up --build
```

Make sure ports `3000`, `6379`, `8000`, `8080`, and `3306` are free before starting.

### Authentication

The API uses Laravel Sanctum bearer tokens. All quote endpoints require authentication.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/register` | Create account |
| `POST` | `/api/auth/login` | Sign in and receive token |
| `POST` | `/api/auth/logout` | Revoke current token |
| `GET` | `/api/auth/me` | Current user |
| `GET` | `/api/quotes` | List saved quotes for the authenticated user |
| `GET` | `/api/quotes/{id}` | Show a saved quote |
| `POST` | `/api/quotes` | Calculate and persist a quote for the authenticated user |
| `PUT` | `/api/quotes/{id}` | Recalculate and update an existing quote |

Quotes and quote travelers are stored with `user_id` for multi-tenant isolation.

Frontend routes:

- `/` — quote list when authenticated, login/register card otherwise
- `/quotes/new` — new quote form
- `/quotes/{id}` — edit an existing quote (updates the same record)
- `/login` and `/register` — redirect to `/`

Services:

| Service     | URL                      |
|-------------|--------------------------|
| Frontend    | http://localhost:3000    |
| Backend     | http://localhost:8000    |
| phpMyAdmin  | http://localhost:8080    |
| MySQL       | localhost:3306           |
| Redis       | localhost:6379           |

**phpMyAdmin credentials**

- Server: `mysql`
- User: `laravel`
- Password: `secret`

Redis is used as Laravel's cache store. Identical quote requests are cached by `CachedQuotePricingService` for one hour (`QUOTE_CACHE_TTL=3600`).

Stop the stack:

```bash
docker compose down
```

## Project Structure

```text
backend/   Laravel API with pricing business rules and PHPUnit tests
frontend/  Next.js app with Zustand, Tailwind CSS, i18n, and dark/light theme
```

## How to Run the Backend (Local)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

The API will be available at `http://localhost:8000`.

## How to Run the Frontend (Local)

```bash
cd frontend
npm install
cp .env.local.example .env.local
npm run dev
```

The app will be available at `http://localhost:3000`.

By default, the frontend calls `http://localhost:8000/api`. You can override this with `NEXT_PUBLIC_API_URL` in `frontend/.env.local`.

## How to Run Tests

Backend (PHPUnit):

```bash
cd backend
composer test
```

Or directly:

```bash
cd backend
./vendor/bin/phpunit
```

Frontend (Vitest):

```bash
cd frontend
npm test
```

### Test coverage summary

| Area | What is covered |
|------|-----------------|
| `QuotePricingService` | Minimum 5 charged days, single-day trip, age at trip start, senior multiplier, destination rates, adventure sports warning, group discount, full multi-traveler scenario |
| `CachedQuotePricingService` | Redis/array cache by request payload |
| `Money` | Half-up rounding |
| `QuoteController` | Auth required, create/list/show/update, validation, ownership, persistence, pagination |
| `QuoteFilter` | Destination and search filters |
| `QuoteRequestData` | DTO mapping from validated input |
| `AuthController` | Register, login, invalid credentials |
| Frontend utils | Quote response parsing, warning translation, form mapping |

## API Request Example

`POST /api/quotes`

```json
{
  "destination": "EUROPE",
  "start_date": "2026-07-10",
  "end_date": "2026-07-20",
  "travelers": [
    {
      "name": "Ana",
      "birth_date": "1990-03-15",
      "add_ons": ["LUGGAGE", "ADVENTURE_SPORTS"]
    },
    {
      "name": "John",
      "birth_date": "1948-11-02",
      "add_ons": ["ADVENTURE_SPORTS", "LUGGAGE"]
    }
  ]
}
```

## API Response Example

```json
{
  "charged_days": 11,
  "travelers": [
    {
      "name": "Ana",
      "age": 36,
      "subtotal": 335.5,
      "applied_add_ons": ["LUGGAGE", "ADVENTURE_SPORTS"]
    },
    {
      "name": "John",
      "age": 77,
      "subtotal": 517.0,
      "applied_add_ons": ["LUGGAGE"]
    }
  ],
  "warnings": [
    {
      "code": "adventure_sports_age_out_of_range",
      "params": {
        "travelerName": "John",
        "minAge": 18,
        "maxAge": 64
      }
    }
  ],
  "group_discount_percentage": 0,
  "final_total": 852.5
}
```

## Decisions and Assumptions

- Business rules live in `QuotePricingService`, keeping the controller thin and validation isolated in `StoreQuoteRequest`.
- List filters live in `QuoteFilter`; the `Quote` model keeps only reusable scopes (`forUser`, `latestForUser`).
- API responses use `QuoteResource`, `QuoteListResource`, and `QuoteTravelerResource` instead of controller transforms.
- Request mapping to DTOs uses `QuoteRequestData::fromArray()`.
- Quote listing uses server-side pagination (`per_page`, default 15) instead of loading all records with `get()`.
- PHP enums represent destination zones and add-ons to avoid magic strings.
- DTOs carry request and response data explicitly without overusing abstractions.
- `Money::roundHalfUp()` centralizes final rounding while intermediate calculations keep full precision.
- Traveler subtotals are rounded only for presentation; the final total uses raw traveler subtotals before discount.
- Applied add-ons preserve the request order among add-ons that were actually applied.
- Adventure sports rejection is treated as a warning, not a validation error.
- Quote responses are cached in Redis through `CachedQuotePricingService`, keyed by the normalized request payload with a configurable TTL.
- The frontend uses [PrimeReact](https://primereact.org/installation/) for UI components, Zustand for auth/theme state, Laravel Precognition for live validation, `next-intl` for English and Portuguese, and theme switching between Lara light/dark themes.
- Quotes are persisted in MySQL with travelers, warnings, and calculation breakdown. Creating a quote redirects to `/quotes/{id}` so subsequent saves update the same record.
- Docker Compose runs backend, frontend, MySQL, Redis, and phpMyAdmin together for local development.

## Challenge Compliance (`CHALLENGE.md`)

| Requirement | Status | Implementation |
|-------------|--------|----------------|
| `POST /api/quotes` with detailed quote response | Done | `QuoteController@store`, `QuotePricingService` |
| Frontend form + detailed quote display | Done | `QuoteForm`, `QuoteSummary`, `TravelersTable` |
| Minimum 5 charged days | Done | `QuotePricingService::resolveChargedDays` + unit test |
| Age calculated at trip start | Done | `calculateAgeAtDate` + unit test |
| Adventure sports denied with warning (not error) | Done | Structured warning + unit/API tests |
| Group discount (10% for 5+ travelers) | Done | Unit test |
| Full scenario with multiple travelers and add-ons | Done | Unit + feature tests (Ana/John, Europe, 852.5 total) |
| Isolated pricing service | Done | `QuotePricingService` / `CachedQuotePricingService` |
| Backend input validation | Done | `StoreQuoteRequest`, `IndexQuoteRequest`, feature tests |
| PHPUnit unit tests for pricing | Done | `tests/Unit/Services/Quotes/QuotePricingServiceTest.php` (9 tests) |
| Frontend consuming API with state management | Done | Zustand auth store, Precognition forms, quote pages |
| README with setup + decisions | Done | This file |
| **Differentiators:** DB persistence + list endpoint | Done | MySQL, `GET /api/quotes`, `QuotesTable` |
| **Differentiators:** Docker Compose | Done | `docker-compose.yml` |
| **Differentiators:** Loading/error states | Done | Spinners, `Message`, auth redirects, table error handling |
| Automated tests beyond pricing unit tests | Done | Feature tests (auth/quotes), frontend Vitest utils |

Field names differ slightly from the challenge suggestion (`start_date` instead of `data_inicio`, etc.), but the response includes charged days, per-traveler subtotals, warnings, group discount, and final total as required.

## What Was Not Implemented

- Email verification and password reset flows
- Admin panel or rate configuration UI
- End-to-end browser tests (Playwright/Cypress)

## Suggested Git Commits

1. `feat(backend): add quote pricing domain with enums, DTOs, and service`
2. `feat(backend): expose POST /api/quotes with validation and JSON response`
3. `test(backend): cover quote pricing rules with unit tests`
4. `feat(frontend): scaffold quote form with Zustand store and API client`
5. `feat(frontend): add i18n, theme toggle, and quote summary UI`
6. `docs: add project README with setup and API examples`
7. `feat(infra): add docker compose with redis cache for quote responses`
