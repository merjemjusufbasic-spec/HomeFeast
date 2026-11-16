<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RecipeDao.php';
require_once __DIR__ . '/../dao/RecipeIngredientDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class RecipeService extends BaseService
{
    protected string $entityName = 'Recipe';
    protected array $rules = [
        'title'        => ['type' => 'string', 'required' => true, 'max' => 150],
        'description'  => ['type' => 'string', 'nullable' => true, 'max' => 5000],
        'instructions' => ['type' => 'string', 'required' => true, 'max' => 20000],
        'prep_time'    => ['type' => 'int', 'nullable' => true, 'min' => 1, 'max' => 1440],   // minutes
        'category_id'  => ['type' => 'int', 'nullable' => true, 'min' => 1],
        'user_id'      => ['type' => 'int', 'required' => true, 'min' => 1],                  // author
    ];

    private RecipeDao $recipeDao;
    private RecipeIngredientDao $ingredientDao;
    private CategoryDao $categoryDao;
    private UserDao $userDao;

    public function __construct()
    {
        $this->recipeDao     = new RecipeDao();
        $this->ingredientDao = new RecipeIngredientDao();
        $this->categoryDao   = new CategoryDao();
        $this->userDao       = new UserDao();
        parent::__construct($this->recipeDao);
    }

    /**
     * List recipes (with category name). Optional filters can be combined:
     * q (title contains), category_id, user_id.
     */
    public function getAll(array $filters = []): array
    {
        $recipes = $this->recipeDao->getAllWithCategory();

        if (!empty($filters['q'])) {
            $q = mb_strtolower($filters['q']);
            $recipes = array_filter($recipes, fn($r) => str_contains(mb_strtolower($r['title']), $q));
        }
        if (!empty($filters['category_id'])) {
            $recipes = array_filter($recipes, fn($r) => (int) $r['category_id'] === (int) $filters['category_id']);
        }
        if (!empty($filters['user_id'])) {
            $recipes = array_filter($recipes, fn($r) => (int) $r['user_id'] === (int) $filters['user_id']);
        }
        return array_values($recipes);
    }

    /** One recipe together with its ingredients. */
    public function getDetails(int $id): array
    {
        $recipe = $this->getById($id);
        $recipe['ingredients'] = $this->ingredientDao->getByRecipe($id);
        return $recipe;
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        // The author of a recipe never changes.
        if ($isUpdate) {
            unset($data['user_id']);
        }
        $clean  = parent::validate($data, $isUpdate, $id);
        $errors = [];

        if (isset($clean['category_id']) && $this->categoryDao->getById($clean['category_id']) === null) {
            $errors['category_id'] = 'Category does not exist.';
        }
        if (isset($clean['user_id']) && $this->userDao->getById($clean['user_id']) === null) {
            $errors['user_id'] = 'User does not exist.';
        }
        $this->fail($errors);
        return $clean;
    }
}
