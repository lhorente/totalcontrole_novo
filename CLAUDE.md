# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Total Controle is a personal/family finance control system, composed of two applications that share the **same MySQL database**:

- **`v1/`** — legacy app, CakePHP 2.x (PHP 7.4). Frozen, do not add features here unless explicitly asked.
- **`v2/`** — active app, Laravel 8 (PHP 8.3) with Jetstream + Fortify + Inertia scaffolding, but **the actual business UI is server-rendered Blade + AdminLTE (Bootstrap), not Vue/Inertia**. All new work should target `v2/`.

Everything below (commands, architecture) refers to `v2/src` unless stated otherwise.

## Running the app (Docker)

The project runs via `docker-compose.yml` at the repo root, which starts three services: `totalcontrole_db` (MySQL 5.7, host port 3310), `total_controle_v1` (port 8091), `total_controle_v2` (port 8092).

```bash
docker compose up -d --build          # start everything
docker compose exec total_controle_v2 bash   # shell into the v2 container
docker compose logs -f total_controle_v2      # tail v2 logs
```

`vendor/` and compiled front-end assets (`public/js`, `public/css`) are committed, so `composer install`/`npm install` are only needed when changing dependencies or recompiling assets.

## Common commands (run inside `total_controle_v2` container, or locally from `v2/src` if PHP 8.3 is available)

```bash
# Artisan
php artisan migrate                          # run migrations
php artisan migrate:rollback                 # rollback last batch
php artisan db:seed
php artisan tinker

# Tests (PHPUnit, config at v2/src/phpunit.xml)
vendor/bin/phpunit                                    # full suite (Unit + Feature)
vendor/bin/phpunit --filter TestClassName              # single test class
vendor/bin/phpunit --filter test_method_name            # single test method
vendor/bin/phpunit tests/Feature/TwoFactorAuthenticationTest.php   # single file

# Custom artisan commands
php artisan transaction-mappings:backfill [--workspace=ID] [--dry-run]
php artisan two-factor:reset {user}          # ResetTwoFactorAuthentication command

# Front-end (webpack via laravel-mix, Vue 2 — only used by Jetstream/auth pages)
npm run dev          # development build
npm run watch
npm run production   # production build
```

Prefix any of the above with `docker compose exec total_controle_v2` when running from the host.

## Architecture

### Two rendering stacks coexist in v2

- **Business modules** (transactions, categories, wallets, contacts, credit cards, transaction mappings, documentos) are plain Laravel MVC: `Controller` → `return view('module/action', [...])` → Blade template under `resources/views/<module>/`, styled with AdminLTE/Bootstrap and jQuery-ish plugins (`resources/views/layouts/dashboard.blade.php`). No Inertia, no Vue, no client-side routing here.
- **Jetstream/Fortify scaffolding** (login, register, profile, 2FA, API tokens) uses Inertia + Vue 2 (`resources/views/app.blade.php` with `@inertia`, pages under `resources/js/Pages`). Don't mix the two patterns — new CRUD-style features should follow the Blade pattern used by the rest of the app, not the Jetstream/Inertia one.

### Multi-tenancy: Workspaces

- Every user belongs to one or more `workspaces` (table `workspaces`, pivot `workspace_users`, models `Workspace`/`WorkspaceUser`).
- `SetActiveWorkspace` middleware (aliased `workspace` in `app/Http/Kernel.php`) runs on every authenticated route group, defaults the session to the user's first active `pessoal` workspace, and shares `activeWorkspace`/`userWorkspaces` to all views.
- Data isolation is enforced via `CurrentUserScope` (`app/Models/Scopes/CurrentUserScope.php`), a global Eloquent scope that filters by `id_workspace` from `session('active_workspace_id')`. It's applied via `static::addGlobalScope(new CurrentUserScope)` in `booted()` on models like `Transaction` and `TransactionMapping`. **Any new model scoped to a workspace must add this same global scope**, and any cross-workspace query (e.g. background jobs, artisan commands) must explicitly `withoutGlobalScope(CurrentUserScope::class)` and filter `id_workspace` manually — see `BackfillTransactionMappings` and `TransactionMapping::matchFor()` for the pattern.

### Auth

- Login is by **`document`** (CPF/CNPJ), not email — see `config/fortify.php` (`'username' => 'document'`) and `App\Models\User`.
- 2FA is mandatory: `EnsureTwoFactorIsEnabled` middleware (alias `two_factor.enabled`) redirects to `profile.show` if `two_factor_confirmed_at` is null. The profile route is intentionally kept outside this middleware group to avoid a redirect loop (see comment in `routes/web.php`).

### "De <> Para" (transaction mapping / auto-categorization)

- `TransactionMapping` (table `de_para_transacoes`) maps a raw bank description pattern (`padrao`, normalized into `padrao_normalizado` — uppercase, no accents, collapsed whitespace) to a local nickname (`descricao_local`) and category, per workspace.
- `TransactionObserver::saved()` auto-feeds this table whenever a `Transaction` is created/renamed with a `descricao` different from `descricao_banco`, via `TransactionMapping::learnFrom()`. Auto-created mappings (`origem = 'automatico'`) get overwritten by newer edits; manually-created mappings (from the De <> Para screen) are only usage-reinforced, never overwritten.
- `TransactionMapping::matchFor()` finds the mapping with the longest matching `padrao_normalizado` contained in a bank description — used during CSV import to suggest categorization.
- `transaction-mappings:backfill` artisan command retroactively runs `learnFrom()` over existing transactions whose `descricao` differs from `descricao_banco`.

### Transaction import / dedup

- CSV imports go through `CsvParserService` (Brazilian number format parsing, e.g. `"1.234,56"` → `1234.56`) and `SmartposImportController`/`TransactionsController::import*`.
- Imported/bank-matched transactions get a `chave_banco` (`Transaction::generateChaveBanco()`), an MD5 of `dataBanco|descricao|valor|dataFatura(Y-m)`, used to dedupe re-imports.

### Documentos e Prazos module

- Generic "document/deadline with event" model: `Evento` + `EventoDocumento` (see migrations `2026_08_15_*`), exposed via `DocumentoController` under the `documentos.*` route group/prefix.

## Database

- Both v1 (CakePHP) and v2 (Laravel) read/write the **same MySQL schema**. Table names are largely Portuguese/legacy (`transacoes`, `clientes`, `categorias`, `de_para_transacoes`) since v2 was built on top of v1's schema — don't assume Laravel default naming conventions when writing raw queries or migrations.
- Migrations live in `v2/src/database/migrations`; run them from the v2 container. v1 has no migration system (schema managed manually / via v2 migrations).
