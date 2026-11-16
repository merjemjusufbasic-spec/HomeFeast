<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/FavoriteService.php';

/**
 * Routes for /favorites plus the per-user helpers under /users/{id}/favorites.
 */
class FavoriteRoutes
{
    public static function register(): void
    {
        Flight::register('favoriteService', FavoriteService::class);

        Flight::route('GET /favorites',                                          [self::class, 'index']);
        Flight::route('GET /favorites/@id:[0-9]+',                               [self::class, 'show']);
        Flight::route('POST /favorites',                                         [self::class, 'store']);
        Flight::route('PUT /favorites/@id:[0-9]+',                               [self::class, 'update']);
        Flight::route('DELETE /favorites/@id:[0-9]+',                            [self::class, 'destroy']);
        Flight::route('GET /users/@id:[0-9]+/favorites',                         [self::class, 'userFavorites']);
        Flight::route('DELETE /users/@userId:[0-9]+/favorites/@recipeId:[0-9]+', [self::class, 'removeByRecipe']);
    }

    /**
     * @OA\Get(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="List favorites",
     *     @OA\Parameter(name="user_id", in="query", required=false, description="Only the favorites of this user", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of favorites", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Favorite"))),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::favoriteService()->getAllFiltered(RequestHelper::intQuery('user_id')));
    }

    /**
     * @OA\Get(
     *     path="/favorites/{id}",
     *     tags={"Favorites"},
     *     summary="Get one favorite",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Favorite found", @OA\JsonContent(ref="#/components/schemas/Favorite")),
     *     @OA\Response(response=404, description="Favorite not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::favoriteService()->getById((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="Create a favorite",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"user_id", "recipe_id"}, 
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="recipe_id", type="integer", example=3))),
     *     @OA\Response(response=201, description="Favorite created", @OA\JsonContent(ref="#/components/schemas/Favorite")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::favoriteService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/favorites/{id}",
     *     tags={"Favorites"},
     *     summary="Update a favorite (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="recipe_id", type="integer", example=3))),
     *     @OA\Response(response=200, description="Favorite updated", @OA\JsonContent(ref="#/components/schemas/Favorite")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Favorite not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::favoriteService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/favorites/{id}",
     *     tags={"Favorites"},
     *     summary="Delete a favorite",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Favorite deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Favorite deleted."))),
     *     @OA\Response(response=404, description="Favorite not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::favoriteService()->delete((int) $id);
        Flight::json(['message' => 'Favorite deleted.']);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}/favorites",
     *     tags={"Favorites"},
     *     summary="A user's favorites as full recipes",
     *     @OA\Parameter(name="id", in="path", required=true, description="User id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Favorite recipes, newest first", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/FavoriteRecipe"))),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function userFavorites($id): void
    {
        Flight::json(Flight::favoriteService()->getRecipesForUser((int) $id));
    }

    /**
     * @OA\Delete(
     *     path="/users/{userId}/favorites/{recipeId}",
     *     tags={"Favorites"},
     *     summary="Remove a recipe from a user's favorites",
     *     @OA\Parameter(name="userId", in="path", required=true, description="User id", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="recipeId", in="path", required=true, description="Recipe id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Favorite removed", @OA\JsonContent(@OA\Property(property="message", type="string", example="Favorite deleted."))),
     *     @OA\Response(response=404, description="User or favorite not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function removeByRecipe($userId, $recipeId): void
    {
        Flight::favoriteService()->removeByUserAndRecipe((int) $userId, (int) $recipeId);
        Flight::json(['message' => 'Favorite deleted.']);
    }
}
