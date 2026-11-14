<?php
require_once __DIR__ . '/RequestHelper.php';
require_once __DIR__ . '/../services/UserService.php';

/**
 * Routes for /users. The password is hashed by UserService and never returned.
 */
class UserRoutes
{
    public static function register(): void
    {
        Flight::register('userService', UserService::class);

        Flight::route('GET /users',               [self::class, 'index']);
        Flight::route('GET /users/@id:[0-9]+',    [self::class, 'show']);
        Flight::route('POST /users',              [self::class, 'store']);
        Flight::route('PUT /users/@id:[0-9]+',    [self::class, 'update']);
        Flight::route('DELETE /users/@id:[0-9]+', [self::class, 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="List users",
     *     @OA\Response(response=200, description="List of users", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function index(): void
    {
        Flight::json(Flight::userService()->getAll());
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Get one user",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="User found", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function show($id): void
    {
        Flight::json(Flight::userService()->getById((int) $id));
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Create a user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"name", "email", "password"}, 
     *             @OA\Property(property="name", type="string", example="Amela Hodzic"),
     *             @OA\Property(property="email", type="string", example="amela@example.com"),
     *             @OA\Property(property="password", type="string", example="Secret123!"),
     *             @OA\Property(property="role", type="string", example="user", enum={"admin","user"}))),
     *     @OA\Response(response=201, description="User created", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function store(): void
    {
        Flight::json(Flight::userService()->create(RequestHelper::body()), 201);
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Update a user (send only the fields to change)",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Amela Hodzic"),
     *             @OA\Property(property="email", type="string", example="amela@example.com"),
     *             @OA\Property(property="password", type="string", example="Secret123!"),
     *             @OA\Property(property="role", type="string", example="user", enum={"admin","user"}))),
     *     @OA\Response(response=200, description="User updated", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=400, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function update($id): void
    {
        Flight::json(Flight::userService()->update((int) $id, RequestHelper::body()));
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     @OA\Parameter(name="id", in="path", required=true, description="Record id", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="User deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="User deleted."))),
     *     @OA\Response(response=400, description="Last admin cannot be deleted", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public static function destroy($id): void
    {
        Flight::userService()->delete((int) $id);
        Flight::json(['message' => 'User deleted.']);
    }
}
