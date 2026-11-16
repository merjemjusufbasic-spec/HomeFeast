<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/MealDbService.php';

/**
 * Backend endpoints for the external API (TheMealDB).
 * The frontend calls these; it never talks to TheMealDB directly,
 * so the API key stays on the server and failures are logged in one place.
 */
class MealRoutes
{
    public static function register(): void
    {
        Flight::register('mealDbService', MealDbService::class);

        Flight::route('GET /external/meals/search',        [self::class, 'search']);
        Flight::route('GET /external/meals/random',        [self::class, 'random']);
        Flight::route('GET /external/meals/@id:[0-9]+',    [self::class, 'show']);
    }

    /**
     * @OA\Get(
     *     path="/external/meals/search",
     *     tags={"External API (TheMealDB)"},
     *     summary="Search TheMealDB recipes by name",
     *     @OA\Parameter(name="q", in="query", required=true, description="Text to search for, e.g. pasta", @OA\Schema(type="string", example="pasta")),
     *     @OA\Response(response=200, description="Matching meals (empty list when nothing matches)", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Meal"))),
     *     @OA\Response(response=400, description="Missing or too long search text", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=503, description="TheMealDB is unreachable or timed out", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function search(): void
    {
        Flight::json(Flight::mealDbService()->search((string) RequestHelper::query('q')));
    }

    /**
     * @OA\Get(
     *     path="/external/meals/random",
     *     tags={"External API (TheMealDB)"},
     *     summary="One random meal (Meal of the day)",
     *     @OA\Response(response=200, description="A random meal", @OA\JsonContent(ref="#/components/schemas/Meal")),
     *     @OA\Response(response=503, description="TheMealDB is unreachable or timed out", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function random(): void
    {
        Flight::json(Flight::mealDbService()->random());
    }

    /**
     * @OA\Get(
     *     path="/external/meals/{id}",
     *     tags={"External API (TheMealDB)"},
     *     summary="Get one TheMealDB meal by its id",
     *     @OA\Parameter(name="id", in="path", required=true, description="TheMealDB meal id", @OA\Schema(type="integer", example=52772)),
     *     @OA\Response(response=200, description="The meal", @OA\JsonContent(ref="#/components/schemas/Meal")),
     *     @OA\Response(response=404, description="No meal with this id", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=503, description="TheMealDB is unreachable or timed out", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::mealDbService()->getById((string) $id));
    }
}
