<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/FavoriteDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/RecipeDao.php';

class FavoriteService extends BaseService
{
    protected string $entityName = 'Favorite';
    protected array $rules = [
        'user_id'   => ['type' => 'int', 'required' => true, 'min' => 1],
        'recipe_id' => ['type' => 'int', 'required' => true, 'min' => 1],
    ];

    private FavoriteDao $favoriteDao;
    private UserDao $userDao;
    private RecipeDao $recipeDao;

    public function __construct()
    {
        $this->favoriteDao = new FavoriteDao();
        $this->userDao     = new UserDao();
        $this->recipeDao   = new RecipeDao();
        parent::__construct($this->favoriteDao);
    }

    /** Raw favorite rows, optionally only those of one user. */
    public function getAllFiltered(?int $userId): array
    {
        $rows = $this->favoriteDao->getAll();
        if ($userId !== null) {
            $rows = array_values(array_filter($rows, fn($f) => (int) $f['user_id'] === $userId));
        }
        return $rows;
    }

    /** A user's favorites as full recipes (for the "Favorites" page). */
    public function getRecipesForUser(int $userId): array
    {
        $this->requireUser($userId);
        return $this->favoriteDao->getByUser($userId);
    }

    /** Un-favorite using user + recipe (what a heart button knows). */
    public function removeByUserAndRecipe(int $userId, int $recipeId): void
    {
        $this->requireUser($userId);
        if (!$this->favoriteDao->deleteByUserAndRecipe($userId, $recipeId)) {
            throw new ApiException('Favorite not found.', 404);
        }
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        // A favorite always belongs to the user who created it; only the recipe can change.
        if ($isUpdate) {
            unset($data['user_id']);
        }
        $clean  = parent::validate($data, $isUpdate, $id);
        $errors = [];

        if (isset($clean['user_id']) && $this->userDao->getById($clean['user_id']) === null) {
            $errors['user_id'] = 'User does not exist.';
        }
        if (isset($clean['recipe_id']) && $this->recipeDao->getById($clean['recipe_id']) === null) {
            $errors['recipe_id'] = 'Recipe does not exist.';
        }
        $this->fail($errors);

        // Business rule: a recipe can be favorited only once per user.
        if (isset($clean['recipe_id'])) {
            $ownerId = $isUpdate ? (int) $this->dao->getById($id)['user_id'] : $clean['user_id'];
            if ($this->favoriteDao->exists($ownerId, $clean['recipe_id'])) {
                $this->fail(['recipe_id' => 'This recipe is already in favorites.']);
            }
        }
        return $clean;
    }

    private function requireUser(int $userId): void
    {
        if ($this->userDao->getById($userId) === null) {
            throw new ApiException('User not found.', 404);
        }
    }
}
