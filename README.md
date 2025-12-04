## Laravel API Boilerplate

Laravel 12 REST API starter with Sanctum token auth, Docker (Sail) + PostgreSQL, and auto-generated OpenAPI docs using Scramble.

### Stack
- Laravel 12
- Sanctum (API tokens)
- Sail (Docker)
- PostgreSQL
- Scramble (OpenAPI docs generator)

### Quick start
1) Prerequisites
- Docker Desktop installed and running

2) Start the containers
```bash
./vendor/bin/sail up -d
```

3) Run database migrations
```bash
./vendor/bin/sail artisan migrate
```

4) Open the app
- App: http://localhost:8080
- API Docs UI: http://localhost:8080/docs/api
- OpenAPI JSON: http://localhost:8080/docs/api.json

### Environment
This repo already includes a working `.env` tuned for Sail + Postgres.

Key values:
- `APP_PORT=8080` (app port)
- `VITE_PORT=5300` (Vite dev server, if used)
- `FORWARD_DB_PORT=5433` (host port that forwards to container's 5432)
- `DB_CONNECTION=pgsql`
- `DB_HOST=pgsql`
- `DB_PORT=5432`
- `DB_DATABASE=laravel`
- `DB_USERNAME=sail`
- `DB_PASSWORD=password`

If a port is taken on your machine, change it in `.env` and re-run:
```bash
./vendor/bin/sail up -d
```

### Auth endpoints (Sanctum tokens)
- Login: `POST /api/auth/login`
- Logout: `POST /api/auth/logout` (requires auth)
- Current user: `GET /api/auth/me` (requires auth)
- Forgot password: `POST /api/auth/forgot-password`
- Reset password: `POST /api/auth/reset-password`

Example requests
```bash
# Login (returns { user, accessToken, tokenType, expiresIn })
curl -X POST http://localhost:8080/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@example.com","password":"Test@1234"}'

# Authenticated request
curl http://localhost:8080/api/auth/me \
  -H "Authorization: Bearer <token>"

# Logout current token
curl -X POST http://localhost:8080/api/auth/logout \
  -H "Authorization: Bearer <token>"
```

### API documentation (Scramble)
- UI: http://localhost:8080/docs/api
- JSON: http://localhost:8080/docs/api.json

Export the OpenAPI JSON to a file (optional):
```bash
./vendor/bin/sail artisan scramble:export --path=public/openapi.json
```

### Common commands
```bash
# Artisan (inside containers)
./vendor/bin/sail artisan <command>

# Run tests
./vendor/bin/sail test

# Code style (Laravel Pint)
./vendor/bin/sail pint

# Stop containers
./vendor/bin/sail down

# Rebuild app image (after PHP extensions or runtime changes)
./vendor/bin/sail build --no-cache
```

### 🚀 Generate New APIs from OpenAPI Specs

This project includes powerful automation tools to generate complete APIs from YAML specifications:

#### Option 1: Complete Workflow (Claude Code Slash Command)

**Recommended for end-to-end automation:**

```bash
/generate-api docs/api/your-api-spec.yaml
```

**What it does:**
- ✅ Generates Form Requests with validation
- ✅ Generates API Resources for responses
- ✅ Generates Controllers with all endpoints
- ✅ Updates `routes/api.php` automatically
- ✅ Creates comprehensive frontend integration documentation
- ✅ Runs migrations and creates test data
- ✅ Provides complete summary and next steps

**Example:**
```bash
/generate-api docs/api/trainers-api.yaml
/generate-api docs/api/workouts-api.yaml
```

#### Option 2: Backend Code Only (Artisan Command)

**For generating backend code without documentation:**

```bash
./vendor/bin/sail artisan app:generate-from-openapi docs/api/your-api-spec.yaml
```

**What it generates:**
- Form Request classes
- API Resource classes
- Controller with method stubs
- Route definitions (in routes/generated_routes.txt)

**Note:** Routes must be manually added to `routes/api.php`

### Frontend Developer Documentation

Comprehensive integration guides for frontend developers are available in `docs/frontend/`:

- **Index**: `docs/frontend/README.md`
- **Authentication API**: `docs/frontend/Authentication-API-Integration-Guide.md`

Each guide includes:
- Quick start instructions
- Authentication flow diagrams
- Complete endpoint documentation with examples
- Code examples for React, Vue.js, and JavaScript
- Error handling guide
- Best practices
- Testing checklist

### Project Architecture

#### Database
- **UUID Primary Keys** - All models use UUIDs instead of auto-increment IDs
- **PostgreSQL** - Production-ready relational database
- **Migrations** - Version-controlled schema changes

#### Authentication
- **Laravel Sanctum** - Token-based API authentication
- **Token Expiration** - 1 hour (3600 seconds)
- **Rate Limiting** - 6 requests/minute on sensitive endpoints
- **No Public Registration** - Users created by administrators only

#### Code Organization
- **Form Requests** - Validation logic separated from controllers
- **API Resources** - Consistent JSON response formatting
- **Enums** - Type-safe role definitions (user, admin, trainer)
- **Service Layer Ready** - Clean architecture for business logic

#### Standards
- **PSR-12** - Coding style standards
- **camelCase** - JSON responses (frontend-friendly)
- **snake_case** - Database columns (Laravel convention)
- **Security First** - Input validation, password hashing, rate limiting

### Project notes
- API routes are defined in `routes/api.php`.
- The `User` model uses `Laravel\Sanctum\HasApiTokens` for token issuing.
- All users have UUID primary keys for better scalability and security.
- Scramble routes are auto-registered for `/docs/api` and `/docs/api.json`.

### Troubleshooting
- Port already in use: update `APP_PORT`, `VITE_PORT`, or `FORWARD_DB_PORT` in `.env`, then re-run `./vendor/bin/sail up -d`.
- DB connection errors: ensure containers are up and `.env` matches the values listed above.

### Additional Documentation

- **Implementation Summary**: `docs/IMPLEMENTATION_SUMMARY.md` - Complete overview of auth API implementation
- **Frontend Guides**: `docs/frontend/` - Integration guides for frontend developers
- **OpenAPI Specs**: `docs/api/` - YAML specifications for all APIs

### Support

For questions or issues:
- Review the documentation in `docs/`
- Check the auto-generated API docs at http://localhost:8080/docs/api
- Refer to Laravel Sanctum documentation: https://laravel.com/docs/sanctum
