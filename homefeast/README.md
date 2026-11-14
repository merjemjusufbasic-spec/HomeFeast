# HomeFeast
Homefeast is a Single-Page Application (SPA) designed to help users manage their kitchen inventory and discover new recipes. It tracks available ingredients and suggests meals to cook based on what you already have.

**Features**
* **Pantry Management:** Track your ingredients and expiration dates.
* **Recipe Exploration:** Browse recipes by category or search using TheMealDB.
* **Favorites & Reviews:** Save your top meals and leave ratings.
* **Meal of the Day:** Discover a new featured recipe daily.

**Project Structure**
* `frontend/`: Contains the SPA logic (`app.js`), images (`assets/`), and HTML templates (`views/`).
* `backend/`: Reserved for future server-side and database implementation.
* `docs/`: Administrative documentation, including the AI Log (`AI_LOG.md`) and database schema (`ERD.md`).

**Technologies Used**
* Frontend: HTML5, JavaScript (Vanilla), CSS3
* UI Framework: Bootstrap 5
* External API: TheMealDB

**Status: Milestone 3** – services, REST routes, OpenAPI/Swagger and the external API. Authentication, middleware/logging and the connected frontend come in Milestone 4.

## Features
- **Pantry management:** track ingredients and quantities.
- **Recipe exploration:** browse recipes by category; search TheMealDB.
- **Favorites:** save your top meals.
- **What can I cook?:** recipes ranked by how many ingredients you already have.

## Entities and relationships
`users`, `categories`, `recipes`, `recipe_ingredients`, `pantry`, `favorites`. See [docs/ERD.md](docs/ERD.md).

## Roles and test accounts
| Role | Can do | Email | Password |
|---|---|---|---|
| admin | Full CRUD on all entities | admin@homefeast.com | `Admin123!` |
| user | Manage own pantry/favorites, create recipes, view everything else | john@example.com | `User123!` |

(Jane Smith, jane@example.com, is a second user account with the same password as John.)

## External API
**TheMealDB** (https://www.themealdb.com): used to search recipes and show a "Meal of the day".
- Backend service: `backend/services/MealDbService.php`; endpoints `GET /external/meals/search?q=`, `GET /external/meals/random`, `GET /external/meals/{id}`.
- The frontend only talks to our own API, never to TheMealDB directly (the key stays in `config.php`).
- Failure handling: 3 s connect timeout and `EXTERNAL_API_TIMEOUT` (default 5 s) total timeout; timeouts, HTTP errors and invalid responses are written to `backend/logs/error.log`, successful calls to `backend/logs/info.log`, and the client gets a friendly `503` message. The rest of the app keeps working without it.

## Tech stack
PHP + FlightPHP (REST), PDO, MySQL/MariaDB, JWT; HTML, CSS, JavaScript, Bootstrap 5.

## Running locally
1. Import the database: `backend/database/homefeast.sql` (it creates the `homefeast` database), e.g. via phpMyAdmin or
   `mysql -u root -p < backend/database/homefeast.sql`.
2. Copy `backend/config/config.example.php` to `backend/config/config.php` and fill in your DB credentials (it also contains the TheMealDB settings; the default key `1` is TheMealDB's free test key).
3. Install the PHP dependencies (FlightPHP, swagger-php): `cd backend && composer install`
4. Start the API: `cd backend && php -S localhost:8000 index.php` (or put the project in XAMPP's `htdocs`; `backend/.htaccess` handles the routing).
5. Open the Swagger docs at http://localhost:8000/docs and try the endpoints.
6. Optional DAO smoke test: `cd backend/dao && php test_dao.php`
7. Frontend: serve the `frontend/` folder with any web server (e.g. `cd frontend && php -S localhost:8000`), because views are loaded with `fetch`.

## DAO layer (Milestone 2)
`backend/dao/BaseDao.php` provides shared `getAll`, `getById`, `insert`, `update`, `delete` using PDO prepared statements. Each entity has its own DAO extending it: `UserDao`, `CategoryDao`, `RecipeDao`, `RecipeIngredientDao`, `PantryDao`, `FavoriteDao`.

## Service layer and API (Milestone 3)
Request flow: `route -> service -> DAO -> database`.
- `backend/services/` – one service per entity (`UserService`, `CategoryService`, `RecipeService`, `RecipeIngredientService`, `PantryService`, `FavoriteService`) extending `BaseService`. Services hold the business rules and validation and are the only layer that calls DAOs. `MealDbService` wraps the external API.
- `backend/routes/` – FlightPHP routes with CRUD for every entity, returning JSON with correct status codes (200, 201, 400, 404, 500, and 503 when the external API is down). 401/403 arrive with authentication in Milestone 4.
- `backend/middleware/ErrorHandler.php` – converts exceptions into JSON errors (no stack traces, SQL or file paths are exposed); `CorsMiddleware.php` allows the frontend to call the API.

Main business rules: unique user emails and category names; passwords are hashed (bcrypt) and never returned; the last admin cannot be deleted or demoted; foreign keys are checked before saving (400 with a clear message); one pantry row per ingredient per user; a recipe can be favorited once per user; the author of a recipe/pantry item/favorite cannot be changed.

| Resource | Endpoints |
|---|---|
| Users | `GET/POST /users`, `GET/PUT/DELETE /users/{id}` |
| Categories | `GET/POST /categories`, `GET/PUT/DELETE /categories/{id}` |
| Recipes | `GET/POST /recipes` (filters `q`, `category_id`, `user_id`), `GET/PUT/DELETE /recipes/{id}` (GET includes ingredients) |
| Recipe ingredients | `GET/POST /recipe-ingredients`, `GET/PUT/DELETE /recipe-ingredients/{id}`, `GET/POST /recipes/{id}/ingredients` |
| Pantry | `GET/POST /pantry` (filter `user_id`), `GET/PUT/DELETE /pantry/{id}`, `GET /pantry/matches?user_id=` |
| Favorites | `GET/POST /favorites`, `GET/PUT/DELETE /favorites/{id}`, `GET /users/{id}/favorites`, `DELETE /users/{userId}/favorites/{recipeId}` |
| External API | `GET /external/meals/search?q=`, `GET /external/meals/random`, `GET /external/meals/{id}` |

## Documentation
- Swagger / OpenAPI: http://localhost:8000/docs when running locally (raw spec at `/openapi.json`). The spec is generated from the `@OA` annotations above each route handler in `backend/routes/`.
- Deployed application: _added in Milestone 5_

## Project structure
See [AGENTS.md](AGENTS.md). AI usage is documented in [docs/AI_LOG.md](docs/AI_LOG.md).
