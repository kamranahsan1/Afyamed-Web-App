# AfyaMed Backend

Hybrid stack:

| Layer | Technology | Holds |
|---|---|---|
| App auth | Firebase Auth | Patient / doctor / pharmacy login |
| App data | Firebase Firestore | Profiles, family, bookings, orders, … |
| Admin DB | MySQL | `web_admins`, `roles`, `files`, `logs`, `care_plans`, `feedback` |
| Admin UI | Filament (`/admin`) | Control-plane CRUD for MySQL tables |
| Private files | Hostinger disk | `storage/app/private/{category}/` |

Website public front is **not** started yet — only the admin panel.

## Private storage layout

```text
storage/app/private/
  prescriptions/
  insurance/
  medical_reports/
  doctor_documents/
  pharmacy_documents/
```

Use disk `medical` in Laravel. Metadata is stored in MySQL `files`.

## Setup

1. Copy `.env.example` → `.env` and set MySQL + `APP_KEY`
2. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` (minimum 12 characters in production)
3. Place Firebase service account at `storage/app/firebase/service-account.json`
4. Set `FIREBASE_PROJECT_ID`
5. Run:

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

## Admin panel

URL: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

| Email | Password |
|---|---|
| `ADMIN_EMAIL` from `.env` | `ADMIN_PASSWORD` from `.env` |

Auth guard: `web_admin` (MySQL `web_admins`).

### Admin modules

| Module | Notes |
|---|---|
| Admins | Create/edit web admins + assign roles |
| Roles | `super_admin`, `support`, … |
| Care plans | CMS content for the product |
| Feedback | Review app feedback |
| Files | Private file metadata (MySQL) |
| Audit logs | Read-only trail |

## Mobile API

- `GET /api/v1/health` — stack + dependency checks
- `GET /api/v1/me` — requires `Authorization: Bearer <Firebase ID token>`

App users are **not** managed in Filament (they live in Firebase Auth + Firestore).

## Local Firebase bypass

Only for local/testing without credentials:

```env
FIREBASE_AUTH_BYPASS=true
```

Never enable in production.
