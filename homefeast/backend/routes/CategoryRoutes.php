<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/CategoryService.php';

/**
 * Routes for /categories.
 */
class CategoryRoutes
{
    public static function register(): void
    {
        Flight::register('categoryService', CategoryService::class);

        Flight::route('GET /categories',               [self::class, 'index']);
        Flight::route('GET /categories/@id:[0-9]+',    [self::class, 'show']);
        Flight::route('POST /categories',              [self::class, 'store']);
        Flight::route('PUT /categories/@id:[0-9]+',    [self::class, 'update']);
        Flight::route('DELETE /categories/@id:[0-9]+', [self::class, 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="List categories",
     *     @OA\Response(response=200, description="List of categories", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::categoryService()->getAll());
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Get one category",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Category found", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::categoryService()->getById((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Create a category",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"name"}, 
     *             @OA\Property(property="name", type="string", example="Soups"),
     *             @OA\Property(property="description", type="string", example="Warm and comforting bowls."))),
     *     @OA\Response(response=201, description="Category created", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::categoryService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update a category (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Soups"),
     *             @OA\Property(property="description", type="string", example="Warm and comforting bowls."))),
     *     @OA\Response(response=200, description="Category updated", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::categoryService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Category deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Category deleted."))),
     *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::categoryService()->delete((int) $id);
        Flight::json(['message' => 'Category deleted.']);
    }
}
