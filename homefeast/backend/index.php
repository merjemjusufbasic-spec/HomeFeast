<?php
/**
 * HomeFeast REST API - entry point (FlightPHP).
 *
 * Run locally:   cd backend && php -S localhost:8000 index.php
 * Swagger docs:  http://localhost:8000/docs
 */
ini_set('display_errors', '0');                       // never print PHP errors into API responses
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';       // FlightPHP, swagger-php (installed with Composer)
require_once __DIR__ . '/config/config.php';

require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/middleware/ErrorHandler.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/CategoryRoutes.php';
require_once __DIR__ . '/routes/RecipeRoutes.php';
require_once __DIR__ . '/routes/RecipeIngredientRoutes.php';
require_once __DIR__ . '/routes/PantryRoutes.php';
require_once __DIR__ . '/routes/FavoriteRoutes.php';
require_once __DIR__ . '/routes/MealRoutes.php';
require_once __DIR__ . '/routes/DocsRoutes.php';

CorsMiddleware::handle();
ErrorHandler::register();

UserRoutes::register();
CategoryRoutes::register();
RecipeRoutes::register();
RecipeIngredientRoutes::register();
PantryRoutes::register();
FavoriteRoutes::register();
MealRoutes::register();
DocsRoutes::register();

Flight::start();
