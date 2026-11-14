# AGENTS.md — HomeFeast

Context file for AI coding assistants working on this repository.

## Project description
HomeFeast is a responsive single-page web application for pantry tracking and recipe discovery. Users keep a pantry, browse and favorite recipes, and see which recipes they can cook with what they own. Admins manage all data. Recipe discovery is enriched through the TheMealDB external API.

## Technology stack
- Backend: PHP, FlightPHP (REST API), PDO, MySQL/MariaDB, firebase/php-jwt
- Frontend: HTML, CSS, JavaScript (fetch/AJAX), Bootstrap 5
- API docs: OpenAPI / Swagger
- Local environment: XAMPP

## Architecture rules
1. Backend is pure PHP, frontend is only HTML/CSS/JS. Never mix them.
2. Layers: route -> service -> DAO -> database. Routes never call DAOs; services are the only layer that calls DAOs.
3. All SQL goes through PDO prepared statements. No string-concatenated values. SQL lives only in DAO classes.
4. Every DAO extends `BaseDao`, which holds the shared CRUD methods.
5. Authentication, authorization (role check), request logging and error handling are reusable FlightPHP middleware.
6. Secrets (DB credentials, JWT secret, API keys) live in `backend/config/config.php`, which is gitignored. Only `config.example.php` is committed.
7. External API failures: timeout, log to error log, return a friendly message; the app must keep working.
8. Views contain no AJAX calls; only JS service files talk to the API.
9. Responses are JSON with correct HTTP status codes (200, 201, 400, 401, 403, 404, 500; 503 when an external API is down).
10. Every entity service extends `BaseService`, declares its validation in `$rules`, and adds entity-specific constraints by overriding `validate()`. Services report problems by throwing `ApiException` (its code is the HTTP status). Services never use Flight, `$_GET` or `$_POST`.
11. Route files are classes with static handler methods plus a `register()` method that calls `Flight::route(...)`. Handlers stay thin: read input via `RequestHelper`, call one service method, return `Flight::json(...)`. Handlers do not catch exceptions; `middleware/ErrorHandler.php` does.
12. OpenAPI: every route handler has an `@OA\...` docblock directly above it; shared models live in `routes/OpenApiSpec.php`. Add or change the docblock whenever a route changes. Swagger UI is served at `/docs`, the spec at `/openapi.json`.
13. External APIs are called only from a service in `backend/services/` (cURL with timeouts). The frontend calls our backend endpoint, never the external API. Never log the API key.
14. Code must run on PHP 8.0 (XAMPP): no `readonly`, enums or `new` in initializers.

## Folder structure
```
backend/
  index.php     entry point: loads everything, registers routes, Flight::start()
  .htaccess     Apache rewrite to index.php (XAMPP)
  routes/       one *Routes.php class per resource + OpenApiSpec.php (schemas), DocsRoutes.php (Swagger), RequestHelper.php
  services/     BaseService, one service per entity, MealDbService (external API), ApiException, Logger
  dao/          Database.php, BaseDao + one DAO per entity
  middleware/   ErrorHandler, CorsMiddleware (auth, role check and request logging come in Milestone 4)
  config/       config.example.php (config.php is gitignored)
  database/     homefeast.sql (schema + sample data)
  logs/         info.log and error.log (gitignored)
frontend/
  index.html    app shell
  css/          styles
  js/           router and app scripts
  views/        HTML templates, one per page
  assets/       images
docs/           ERD.md, AI_LOG.md
```

## Naming conventions
- PHP classes: PascalCase, file name matches class (`RecipeDao.php`, `RecipeService.php`, `RecipeRoutes.php`).
- Route handler names: `index`, `show`, `store`, `update`, `destroy`.
- PHP and JS variables: camelCase. DB tables and columns: snake_case, plural table names.
- REST routes: plural nouns, lowercase (`/recipes`, `/pantry`).

## Roles
- `admin`: full CRUD on every entity.
- `user`: manage own pantry and favorites, create recipes, view the rest.

## Rules for the AI
- Explain any code you generate so the author can defend it.
- Do not add libraries or frameworks without asking (current Composer packages: flightphp/core, zircote/swagger-php, doctrine/annotations if swagger-php needs it to read docblocks).
- Never commit or print secrets.
- Keep changes scoped to the current milestone branch.
