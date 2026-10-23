# AGENTS.md

## Project Overview
HomeFeast: A responsive single-page web application (SPA) for recipe management, pantry tracking, meal planning, and user profiles[cite: 1, 3].
- Roles: Regular User, Admin[cite: 1].
- Entities: Users, Recipes, Ingredients, Pantry, Favorites[cite: 1].

## Technology Stack
- Frontend: Single-Page Application (SPA) using HTML5, CSS3, JavaScript (AJAX/Fetch), and Bootstrap 5[cite: 1, 2].
- Backend: PHP with FlightPHP framework (REST API), PHP PDO[cite: 1].
- Database: MySQL / MariaDB[cite: 1, 2].
- Local Environment: XAMPP[cite: 2].

## Folder Structure
- `frontend/`: 
  - `css/`: Custom styles and stylesheets[cite: 1, 2].
  - `js/`: JavaScript logic (`app.js`) for SPA routing and AJAX calls[cite: 1, 2].
  - `views/`: HTML template components loaded dynamically (`login.html`, `dashboard.html`, `pantry.html`, `recipes.html`, etc.)[cite: 1, 3].
  - `static/`: Static assets (images, icons)[cite: 1, 2].
- `backend/`:
  - `routes/`: FlightPHP REST API endpoints[cite: 1].
  - `services/`: Business logic and validation layer[cite: 1].
  - `dao/`: Data Access Object layer for direct database queries using PDO prepared statements[cite: 1].
  - `middleware/`: Authentication (JWT), authorization, logging, and error handling middleware[cite: 1].
  - `config/`: Application configuration (`config.php` ignored by Git, `config.example.php` committed)[cite: 1, 3].
  - `logs/`: Application info and error log files[cite: 1, 3].
- `docs/`: `AI_LOG.md` and project documentation[cite: 1, 3].

## Frontend Guidelines
- All UI views are located in `frontend/views/`[cite: 1, 3].
- Navigation must operate as a Single Page Application (SPA) without reloading the page[cite: 1, 2].
- Use Bootstrap 5 classes for responsive layout and styling; avoid unnecessary inline styles[cite: 1, 2].
- AJAX/Fetch calls must only exist in JS service/controller files, not directly inside HTML view files[cite: 1].

## Backend Guidelines
- Follow the strict architecture chain: `Route -> Service -> DAO -> Database`[cite: 1].
- Routes must return clean JSON output with appropriate HTTP status codes (200, 201, 400, 401, 403, 404, 500)[cite: 1].
- Use PDO prepared statements for all SQL queries[cite: 1].
- Do not mix frontend and backend code[cite: 1].

## Naming & Style Conventions
- PHP Classes: `PascalCase` (e.g., `RecipeService.php`, `UserDao.php`)[cite: 1].
- JavaScript & PHP variables: `camelCase`[cite: 3].
- Database tables & columns: `snake_case`[cite: 3].

## Rules for AI Assistants
- Always adhere to the project folder structure defined above[cite: 1, 3].
- Do not add new external libraries or JS frameworks without prior approval[cite: 1, 3].
- Never write plain text database queries outside DAO classes[cite: 1, 3].
- Do not expose database credentials, JWT secrets, or API keys directly in code[cite: 1, 3].
- Explain changes clearly and suggest small, incremental edits[cite: 3].