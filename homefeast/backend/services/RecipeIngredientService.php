<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RecipeIngredientDao.php';
require_once __DIR__ . '/../dao/RecipeDao.php';

class RecipeIngredientService extends BaseService
{
    protected string $entityName = 'Recipe ingredient';
    protected array $rules = [
        'recipe_id'       => ['type' => 'int', 'required' => true, 'min' => 1],
        'ingredient_name' => ['type' => 'string', 'required' => true, 'max' => 100],
        'quantity'        => ['type' => 'string', 'nullable' => true, 'max' => 50],   // e.g. "250g", "2 cloves"
    ];

    private RecipeIngredientDao $ingredientDao;
    private RecipeDao $recipeDao;

    public function __construct()
    {
        $this->ingredientDao = new RecipeIngredientDao();
        $this->recipeDao     = new RecipeDao();
        parent::__construct($this->ingredientDao);
    }

    /** All ingredients, optionally only those of one recipe. */
    public function getAllFiltered(?int $recipeId): array
    {
        return $recipeId === null ? $this->getAll() : $this->getByRecipe($recipeId);
    }

    public function getByRecipe(int $recipeId): array
    {
        $this->requireRecipe($recipeId);
        return $this->ingredientDao->getByRecipe($recipeId);
    }

    /** POST /recipes/{id}/ingredients - the recipe id comes from the URL. */
    public function createForRecipe(int $recipeId, array $data): array
    {
        $this->requireRecipe($recipeId);
        $data['recipe_id'] = $recipeId;
        return $this->create($data);
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        // An ingredient cannot be moved to another recipe.
        if ($isUpdate) {
            unset($data['recipe_id']);
        }
        $clean = parent::validate($data, $isUpdate, $id);
        if (isset($clean['recipe_id'])) {
            $this->requireRecipe($clean['recipe_id'], 400);
        }
        return $clean;
    }

    private function requireRecipe(int $recipeId, int $status = 404): void
    {
        if ($this->recipeDao->getById($recipeId) === null) {
            throw new ApiException('Recipe not found.', $status);
        }
    }
}
