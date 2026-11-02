<?php
require_once __DIR__ . '/BaseDao.php';

class RecipeIngredientDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('recipe_ingredients', ['recipe_id', 'ingredient_name', 'quantity']);
    }

    public function getByRecipe(int $recipeId): array
    {
        return $this->query(
            'SELECT * FROM recipe_ingredients WHERE recipe_id = :rid',
            ['rid' => $recipeId]
        );
    }
}
