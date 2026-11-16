<?php

/**
 * OpenAPI (Swagger) general information and shared data models.
 * This file contains documentation only - no executable code.
 * Every endpoint is documented right above its handler in the *Routes.php files.
 *
 * @OA\Info(
 *     title="HomeFeast API",
 *     version="1.0.0",
 *     description="REST API for HomeFeast: pantry tracking and recipe discovery. Milestone 3 - business logic, CRUD routes and the TheMealDB external API. Authentication (JWT) is added in Milestone 4."
 * )
 */
class OpenApiSpec
{
}

/**
 * @OA\Schema(schema="Error",
 *     @OA\Property(property="error", type="string", example="Validation failed."),
 *     @OA\Property(property="details", type="object", example={"email": "This email is already registered."}, description="Per-field messages (only for validation errors)")
 * )
 */
class ErrorSchema
{
}

/**
 * @OA\Schema(schema="User",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Amela Hodzic"),
 *     @OA\Property(property="email", type="string", example="amela@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin","user"}, example="user"),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29")
 * )
 */
class UserSchema
{
}

/**
 * @OA\Schema(schema="Category",
 *     @OA\Property(property="id", type="integer", example=3),
 *     @OA\Property(property="name", type="string", example="Vegetarian"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Plant-based delicious dishes."),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29")
 * )
 */
class CategorySchema
{
}

/**
 * @OA\Schema(schema="Recipe",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Chicken Alfredo"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Creamy garlic parmesan pasta."),
 *     @OA\Property(property="instructions", type="string", example="1. Boil pasta. 2. Cook chicken."),
 *     @OA\Property(property="prep_time", type="integer", nullable=true, example=25, description="Minutes"),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="category_name", type="string", nullable=true, example="Italian", description="Only present in the list endpoint"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29")
 * )
 */
class RecipeSchema
{
}

/**
 * @OA\Schema(schema="RecipeDetails",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Chicken Alfredo"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Creamy garlic parmesan pasta."),
 *     @OA\Property(property="instructions", type="string", example="1. Boil pasta. 2. Cook chicken."),
 *     @OA\Property(property="prep_time", type="integer", nullable=true, example=25),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29"),
 *     @OA\Property(property="ingredients", type="array", @OA\Items(ref="#/components/schemas/RecipeIngredient"))
 * )
 */
class RecipeDetailsSchema
{
}

/**
 * @OA\Schema(schema="RecipeIngredient",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="recipe_id", type="integer", example=1),
 *     @OA\Property(property="ingredient_name", type="string", example="Heavy Cream"),
 *     @OA\Property(property="quantity", type="string", nullable=true, example="1 cup")
 * )
 */
class RecipeIngredientSchema
{
}

/**
 * @OA\Schema(schema="PantryItem",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="ingredient_name", type="string", example="Chicken Breast"),
 *     @OA\Property(property="quantity", type="number", example=500),
 *     @OA\Property(property="unit", type="string", nullable=true, example="grams"),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29")
 * )
 */
class PantryItemSchema
{
}

/**
 * @OA\Schema(schema="RecipeMatch",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Chicken Alfredo"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Creamy garlic parmesan pasta."),
 *     @OA\Property(property="total_ingredients", type="integer", example=3),
 *     @OA\Property(property="matched_ingredients", type="integer", example=1)
 * )
 */
class RecipeMatchSchema
{
}

/**
 * @OA\Schema(schema="Favorite",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="recipe_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", example="2026-10-30 12:36:29")
 * )
 */
class FavoriteSchema
{
}

/**
 * @OA\Schema(schema="FavoriteRecipe",
 *     @OA\Property(property="favorite_id", type="integer", example=1),
 *     @OA\Property(property="favorited_at", type="string", example="2026-10-30 12:36:29"),
 *     @OA\Property(property="id", type="integer", example=1, description="Recipe id"),
 *     @OA\Property(property="title", type="string", example="Chicken Alfredo"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Creamy garlic parmesan pasta."),
 *     @OA\Property(property="instructions", type="string", example="1. Boil pasta."),
 *     @OA\Property(property="prep_time", type="integer", nullable=true, example=25),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=1)
 * )
 */
class FavoriteRecipeSchema
{
}

/**
 * @OA\Schema(schema="Meal",
 *     description="A meal from TheMealDB in a clean shape",
 *     @OA\Property(property="id", type="string", example="52772"),
 *     @OA\Property(property="title", type="string", example="Teriyaki Chicken Casserole"),
 *     @OA\Property(property="category", type="string", nullable=true, example="Chicken"),
 *     @OA\Property(property="area", type="string", nullable=true, example="Japanese"),
 *     @OA\Property(property="instructions", type="string", nullable=true, example="Preheat oven to 350 degrees F."),
 *     @OA\Property(property="thumbnail", type="string", nullable=true, example="https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg"),
 *     @OA\Property(property="youtube", type="string", nullable=true, example="https://www.youtube.com/watch?v=4aZr5hZXP_s"),
 *     @OA\Property(property="source", type="string", nullable=true, example="https://example.com/recipe"),
 *     @OA\Property(property="ingredients", type="array",
 *         @OA\Items(type="object",
 *             @OA\Property(property="name", type="string", example="Soy Sauce"),
 *             @OA\Property(property="measure", type="string", example="3/4 cup")))
 * )
 */
class MealSchema
{
}
