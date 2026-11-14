<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/RecipeService.php';

/**
 * Routes for /recipes. GET /recipes/{id} also returns the recipe's ingredients.
 */
class RecipeRoutes
{
    public static function register(): void
    {
        Flight::register('recipeService', RecipeService::class);

        Flight::route('GET /recipes',               [self::class, 'index']);
        Flight::route('GET /recipes/@id:[0-9]+',    [self::class, 'show']);
        Flight::route('POST /recipes',              [self::class, 'store']);
        Flight::route('PUT /recipes/@id:[0-9]+',    [self::class, 'update']);
        Flight::route('DELETE /recipes/@id:[0-9]+', [self::class, 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/recipes",
     *     tags={"Recipes"},
     *     summary="List recipes",
     *     @OA\Parameter(name="q", in="query", required=false, description="Only recipes whose title contains this text", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, description="Only recipes in this category", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user_id", in="query", required=false, description="Only recipes created by this user", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of recipes", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Recipe"))),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::recipeService()->getAll([
            'q'           => RequestHelper::query('q'),
            'category_id' => RequestHelper::intQuery('category_id'),
            'user_id'     => RequestHelper::intQuery('user_id'),
        ]));
    }

    /**
     * @OA\Get(
     *     path="/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Get one recipe (with its ingredients)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Recipe found", @OA\JsonContent(ref="#/components/schemas/RecipeDetails")),
     *     @OA\Response(response=404, description="Recipe not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::recipeService()->getDetails((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/recipes",
     *     tags={"Recipes"},
     *     summary="Create a recipe",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"title", "instructions", "user_id"}, 
     *             @OA\Property(property="title", type="string", example="Tomato Soup"),
     *             @OA\Property(property="description", type="string", example="Silky roasted tomato soup."),
     *             @OA\Property(property="instructions", type="string", example="1. Roast tomatoes.\n2. Blend.\n3. Serve."),
     *             @OA\Property(property="prep_time", type="integer", example=30),
     *             @OA\Property(property="category_id", type="integer", example=3),
     *             @OA\Property(property="user_id", type="integer", example=1))),
     *     @OA\Response(response=201, description="Recipe created", @OA\JsonContent(ref="#/components/schemas/Recipe")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::recipeService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Update a recipe (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Tomato Soup"),
     *             @OA\Property(property="description", type="string", example="Silky roasted tomato soup."),
     *             @OA\Property(property="instructions", type="string", example="1. Roast tomatoes.\n2. Blend.\n3. Serve."),
     *             @OA\Property(property="prep_time", type="integer", example=30),
     *             @OA\Property(property="category_id", type="integer", example=3),
     *             @OA\Property(property="user_id", type="integer", example=1))),
     *     @OA\Response(response=200, description="Recipe updated", @OA\JsonContent(ref="#/components/schemas/Recipe")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Recipe not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::recipeService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Delete a recipe",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Recipe deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Recipe deleted."))),
     *     @OA\Response(response=404, description="Recipe not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::recipeService()->delete((int) $id);
        Flight::json(['message' => 'Recipe deleted.']);
    }
}
