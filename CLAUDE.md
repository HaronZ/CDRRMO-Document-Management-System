# CDRRMO Document Management System

Digitizes paper records for a City Disaster Risk Reduction and Management Office (Philippine LGU). Each regular user manages their own files/folders (Explorer-like); the admin has full CRUD over every user, file, and folder, plus audit logging and an optional task-assignment/notification workflow.

## Stack

- Laravel 13 (PHP 8.3) + MySQL 8 in production, SQLite for local dev (Laravel's own default — `database/database.sqlite`)
- Filament v5 (admin panel) + Livewire v4 (hand-built "My Files" explorer for regular users) — installed as current stable; do not downgrade to v4/v3 without a reason, they're superseded
- `spatie/laravel-activitylog` v4.x (audit trail) — pinned to v4 because v5 requires PHP 8.4, which this environment doesn't have yet. Bump both together later if PHP is upgraded.
- No Redis/queue worker/websockets — `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `SESSION_DRIVER=database` (already the Laravel 13 defaults). Keep it this way; this is a low-traffic LGU office app and extra infra just adds ops burden for a solo maintainer.
- Outbound email via Resend (`resend/resend-laravel`) — `MAIL_MAILER=resend`, `RESEND_API_KEY` in `.env` (never commit the real key; `.env.example` only has a placeholder). `MAIL_FROM_ADDRESS` is `onboarding@resend.dev` (Resend's sandbox sender — works immediately, no domain verification) until a real domain is verified in Resend's dashboard.

## Build & run

```
composer install
npm install
php artisan migrate
npm run build      # or `npm run dev` while developing
php artisan serve
```

Windows dev note: PHP (winget `PHP.PHP.8.3`) and Composer (manual install to `%LOCALAPPDATA%\Composer`) were added to the **User** PATH after the fact — a shell opened before that PATH update won't see `php`/`composer`/`node` until it's refreshed. If a command reports them as not found, refresh PATH first:
```powershell
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
```

Windows dev note #2: this winget PHP build has no CA certificate bundle configured, so any outbound HTTPS call (Resend, any API) fails with a cURL SSL error ("unable to get local issuer certificate"). Fixed by downloading `cacert.pem` (from curl.se/ca/cacert.pem) into the PHP install directory and pointing `curl.cainfo` / `openssl.cafile` at it in `php.ini`. If this environment is ever reprovisioned, redo that step.

## Data model

- `users` — `is_admin` boolean flag. Only two roles exist (admin, regular user) — do not add a full roles/permissions package for this.
- `folders` — self-referencing `parent_id` (tree), `owner_id`, soft-deletes (Recycle Bin). Deleting a folder recursively soft-deletes its subfolders and files (see `MyFiles::softDeleteFolderRecursively`); restoring does the same in reverse.
- `files` — `folder_id`, `owner_id`, `current_version_id`, soft-deletes. `assigned_to_user_id` (nullable — optionally tagged user) is added in Phase 5, not yet present.
- `file_versions` — append-only, one row per upload. Never hard-deleted — this is what makes "Replace" non-destructive. Phase 2 only ever creates version 1 (no overwrite path yet); same-name uploads in the same folder auto-suffix (`name (1).ext`) until Phase 4 adds the Replace/Keep-both choice.
- `file_tasks` — `assigned_by`, `assigned_to`, `status` (pending/completed/overwritten/cancelled), `due_date`. The durable, queryable task record — Laravel's `notifications` table is only the transient alert layer on top of it. Not built yet (Phase 5).
- `activity_log` — from spatie/laravel-activitylog. `causer`/`subject`/`event`/`properties`. Standard event vocabulary: `uploaded`, `downloaded`, `renamed`, `deleted`, `restored`, `moved`, `overwritten`, `version_reverted`, `task_assigned`, `task_completed`.

## Key conventions

- Livewire v4 defaults `make:livewire` to single-file components (a `⚡name.blade.php` file mixing PHP + Blade). We deliberately use `php artisan make:livewire Name --class` instead — a separate PHP class (`app/Livewire/Name.php`) and Blade view (`resources/views/livewire/name.blade.php`). Easier to review/diff and more familiar than SFCs for a component this complex.
- Full-page Livewire components (routed directly, e.g. `Route::get('/my-files', MyFiles::class)`) render inside `resources/views/layouts/app.blade.php` automatically (Livewire's default `component_layout` config, `layouts::app` → `resources/views/layouts/`). Don't add `<html>`/`<head>` inside a Livewire component's own view — only the inner content.
- Admins can sign in via either `/admin/login` (Filament's own, gated by `canAccessPanel()`) or the plain shared `/login` (`AuthenticatedSessionController`) — both work, since both just authenticate against the same `users` table. Signing *out* is unified to always land on `/login`: Filament's default logout redirect (`/admin/login`) is overridden by binding a custom `Filament\Auth\Http\Responses\Contracts\LogoutResponse` (`App\Filament\AdminLogoutResponse`, bound in `AppServiceProvider`) so there's one consistent post-logout destination regardless of where you signed out from. `/` itself dispatches guest → `/login`, admin → `/admin`, regular user → `/my-files`.
- Password self-service is split by user type: admins get Filament's built-in `->profile()` page (`/admin/profile`, includes name/email/password with current-password verification, zero custom code). Regular users get a hand-built `ChangePassword` Livewire page at `/account/password` (linked from the My Files header) since they can't access the Filament panel at all.
- New accounts get a `WelcomeNotification` (mail only, not queued — sent synchronously since there's no queue worker) containing their login email and the temporary password the admin typed, triggered from `CreateUser::afterCreate()`. The plaintext password is read from `$this->data['password']` before Filament's form state is discarded — the User model's `hashed` cast only hashes it at the point Eloquent persists the record, so it's still plaintext in `$this->data` at that hook.
- Files are never served from `public/` — stream downloads through an authenticated controller/policy check, never a public disk URL.
- Regular-user file/folder queries are always scoped to `owner_id` via a Policy — never trust a route-model-bound ID alone.
- Admin Filament resources (Users, Files, Folders, Activity Log) are intentionally **unscoped** — that's the "admin has full control" requirement, not a bug.
- Upload name-conflict flow: always three choices only — Replace / Keep both / Cancel — never bundle extra options into that modal. "Replace" inserts a new `file_versions` row and repoints `current_version_id`; it must never `DELETE` the prior version.
- UX target audience is non-technical government office staff, possibly under stress during actual disaster response: icon+text labels (never icon-only), breadcrumbs always visible, delete confirmations name the literal file, soft-delete/Recycle Bin everywhere instead of hard delete, plain language only (no "soft delete"/"version chain" jargon in the UI).

## Roadmap (build in this order)

1. Auth + `is_admin` flag + Filament Users resource
2. Personal "My Files" Livewire explorer (own files only)
3. Admin oversight (Filament Files/Folders resources, unscoped)
4. Versioning + Replace/Keep-both/Cancel conflict modal + version history
5. Task assignment + notification bell
6. Audit logging UI (filterable, CSV export)
7. Deploy (Ubuntu VPS, Nginx, PHP-FPM, MySQL) + 3-2-1 backups + staff UAT

## Deployment target (for later)

Cloud VPS (DigitalOcean/Vultr/Linode, Singapore region) behind Cloudflare — not on-prem, since the office itself is exposed to the disasters this system exists to be resilient against. MySQL in production; SQLite stays local-dev only.
