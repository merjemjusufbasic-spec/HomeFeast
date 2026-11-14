<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/PantryService.php';

/**
 * Routes for /pantry plus GET /pantry/matches ("What can I cook?").
 */
class PantryRoutes
{
    public static function register(): void
    {
        Flight::register('pantryService', PantryService::class);

        Flight::route('GET /pantry',               [self::class, 'index']);
        Flight::route('GET /pantry/@id:[0-9]+',    [self::class, 'show']);
        Flight::route('POST /pantry',              [self::class, 'store']);
        Flight::route('PUT /pantry/@id:[0-9]+',    [self::class, 'update']);
        Flight::route('DELETE /pantry/@id:[0-9]+', [self::class, 'destroy']);
        Flight::route('GET /pantry/matches',       [self::class, 'matches']);
    }

    /**
     * @OA\Get(
     *     path="/pantry",
     *     tags={"Pantry"},
     *     summary="List pantry items",
     *     @OA\Parameter(name="user_id", in="query", required=false, description="Only the pantry of this user", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of pantry items", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PantryItem"))),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::pantryService()->getAllFiltered(RequestHelper::intQuery('user_id')));
    }

    /**
     * @OA\Get(
     *     path="/pantry/{id}",
     *     tags={"Pantry"},
     *     summary="Get one pantry item",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Pantry item found", @OA\JsonContent(ref="#/components/schemas/PantryItem")),
     *     @OA\Response(response=404, description="Pantry item not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::pantryService()->getById((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/pantry",
     *     tags={"Pantry"},
     *     summary="Create a pantry item",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"user_id", "ingredient_name"}, 
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="ingredient_name", type="string", example="Tomatoes"),
     *             @OA\Property(property="quantity", type="number", example=500),
     *             @OA\Property(property="unit", type="string", example="grams"))),
     *     @OA\Response(response=201, description="Pantry item created", @OA\JsonContent(ref="#/components/schemas/PantryItem")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::pantryService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/pantry/{id}",
     *     tags={"Pantry"},
     *     summary="Update a pantry item (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="ingredient_name", type="string", example="Tomatoes"),
     *             @OA\Property(property="quantity", type="number", example=500),
     *             @OA\Property(property="unit", type="string", example="grams"))),
     *     @OA\Response(response=200, description="Pantry item updated", @OA\JsonContent(ref="#/components/schemas/PantryItem")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Pantry item not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::pantryService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/pantry/{id}",
     *     tags={"Pantry"},
     *     summary="Delete a pantry item",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Pantry item deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Pantry item deleted."))),
     *     @OA\Response(response=404, description="Pantry item not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::pantryService()->delete((int) $id);
        Flight::json(['message' => 'Pantry item deleted.']);
    }

    /**
     * @OA\Get(
     *     path="/pantry/matches",
     *     tags={"Pantry"},
     *     summary="What can I cook? Recipes ranked by how many ingredients the user already has",
     *     @OA\Parameter(name="user_id", in="query", required=true, description="The user whose pantry is compared", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Matching recipes, best match first", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/RecipeMatch"))),
     *     @OA\Response(response=400, description="user_id missing or invalid", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function matches(): void
    {
        $userId = RequestHelper::intQuery('user_id');
        if ($userId === null) {
            throw new ApiException('Query parameter "user_id" is required.', 400);
        }
        Flight::json(Flight::pantryService()->getRecipeMatches($userId));
    }
}
