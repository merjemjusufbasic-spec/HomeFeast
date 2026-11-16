<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/RecipeIngredientService.php';

/**
 * Routes for /recipe-ingredients plus the nested /recipes/{id}/ingredients.
 */
class RecipeIngredientRoutes
{
    public static function register(): void
    {
        Flight::register('recipeIngredientService', RecipeIngredientService::class);

        Flight::route('GET /recipe-ingredients',               [self::class, 'index']);
        Flight::route('GET /recipe-ingredients/@id:[0-9]+',    [self::class, 'show']);
        Flight::route('POST /recipe-ingredients',              [self::class, 'store']);
        Flight::route('PUT /recipe-ingredients/@id:[0-9]+',    [self::class, 'update']);
        Flight::route('DELETE /recipe-ingredients/@id:[0-9]+', [self::class, 'destroy']);
        Flight::route('GET /recipes/@id:[0-9]+/ingredients',   [self::class, 'byRecipe']);
        Flight::route('POST /recipes/@id:[0-9]+/ingredients',  [self::class, 'storeForRecipe']);
    }

    /**
     * @OA\Get(
     *     path="/recipe-ingredients",
     *     tags={"Recipe ingredients"},
     *     summary="List recipe ingredients",
     *     @OA\Parameter(name="recipe_id", in="query", required=false, description="Only ingredients of this recipe", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of recipe ingredients", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/RecipeIngredient"))),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::recipeIngredientService()->getAllFiltered(RequestHelper::intQuery('recipe_id')));
    }

    /**
     * @OA\Get(
     *     path="/recipe-ingredients/{id}",
     *     tags={"Recipe ingredients"},
     *     summary="Get one recipe ingredient",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Recipe ingredient found", @OA\JsonContent(ref="#/components/schemas/RecipeIngredient")),
     *     @OA\Response(response=404, description="Recipe ingredient not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::recipeIngredientService()->getById((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/recipe-ingredients",
     *     tags={"Recipe ingredients"},
     *     summary="Create a recipe ingredient",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"recipe_id", "ingredient_name"}, 
     *             @OA\Property(property="recipe_id", type="integer", example=1),
     *             @OA\Property(property="ingredient_name", type="string", example="Garlic"),
     *             @OA\Property(property="quantity", type="string", example="2 cloves"))),
     *     @OA\Response(response=201, description="Recipe ingredient created", @OA\JsonContent(ref="#/components/schemas/RecipeIngredient")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::recipeIngredientService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/recipe-ingredients/{id}",
     *     tags={"Recipe ingredients"},
     *     summary="Update a recipe ingredient (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="ingredient_name", type="string", example="Garlic"),
     *             @OA\Property(property="quantity", type="string", example="2 cloves"))),
     *     @OA\Response(response=200, description="Recipe ingredient updated", @OA\JsonContent(ref="#/components/schemas/RecipeIngredient")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Recipe ingredient not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::recipeIngredientService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/recipe-ingredients/{id}",
     *     tags={"Recipe ingredients"},
     *     summary="Delete a recipe ingredient",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Recipe ingredient deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Recipe ingredient deleted."))),
     *     @OA\Response(response=404, description="Recipe ingredient not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::recipeIngredientService()->delete((int) $id);
        Flight::json(['message' => 'Recipe ingredient deleted.']);
    }

    /**
     * @OA\Get(
     *     path="/recipes/{id}/ingredients",
     *     tags={"Recipe ingredients"},
     *     summary="List the ingredients of one recipe",
     *     @OA\Parameter(name="id", in="path", required=true, description="Recipe id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Ingredients of the recipe", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/RecipeIngredient"))),
     *     @OA\Response(response=404, description="Recipe not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function byRecipe($id): void
    {
        Flight::json(Flight::recipeIngredientService()->getByRecipe((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/recipes/{id}/ingredients",
     *     tags={"Recipe ingredients"},
     *     summary="Add an ingredient to a recipe",
     *     @OA\Parameter(name="id", in="path", required=true, description="Recipe id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"ingredient_name"}, 
     *             @OA\Property(property="ingredient_name", type="string", example="Garlic"),
     *             @OA\Property(property="quantity", type="string", example="2 cloves"))),
     *     @OA\Response(response=201, description="Ingredient created", @OA\JsonContent(ref="#/components/schemas/RecipeIngredient")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Recipe not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function storeForRecipe($id): void
    {
        Flight::json(Flight::recipeIngredientService()->createForRecipe((int) $id, RequestHelper::body()), 201);
    }
}
