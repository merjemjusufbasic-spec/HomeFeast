<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/PantryDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class PantryService extends BaseService
{
    protected string $entityName = 'Pantry item';
    protected array $rules = [
        'user_id'         => ['type' => 'int', 'required' => true, 'min' => 1],
        'ingredient_name' => ['type' => 'string', 'required' => true, 'max' => 100],
        'quantity'        => ['type' => 'number', 'min' => 0, 'max' => 99999999],
        'unit'            => ['type' => 'string', 'nullable' => true, 'max' => 50],
    ];

    private PantryDao $pantryDao;
    private UserDao $userDao;

    public function __construct()
    {
        $this->pantryDao = new PantryDao();
        $this->userDao   = new UserDao();
        parent::__construct($this->pantryDao);
    }

    /** All pantry rows, optionally only those of one user. */
    public function getAllFiltered(?int $userId): array
    {
        if ($userId === null) {
            return array_map([$this, 'normalize'], $this->pantryDao->getAll());
        }
        $this->requireUser($userId);
        return array_map([$this, 'normalize'], $this->pantryDao->getByUser($userId));
    }

    public function getById(int $id): array
    {
        return $this->normalize(parent::getById($id));
    }

    /** "What can I cook?" - recipes ranked by how many ingredients the user already has. */
    public function getRecipeMatches(int $userId): array
    {
        $this->requireUser($userId);
        return array_map(function ($row) {
            $row['total_ingredients']   = (int) $row['total_ingredients'];
            $row['matched_ingredients'] = (int) $row['matched_ingredients'];
            return $row;
        }, $this->pantryDao->getRecipeMatches($userId));
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        // An item always belongs to the user who created it.
        if ($isUpdate) {
            unset($data['user_id']);
        }
        $clean  = parent::validate($data, $isUpdate, $id);
        $errors = [];

        if (isset($clean['user_id']) && $this->userDao->getById($clean['user_id']) === null) {
            $errors['user_id'] = 'User does not exist.';
        }
        $this->fail($errors);

        // Business rule: one pantry row per ingredient per user (case-insensitive).
        if (isset($clean['ingredient_name'])) {
            $ownerId = $isUpdate ? (int) $this->dao->getById($id)['user_id'] : $clean['user_id'];
            foreach ($this->pantryDao->getByUser($ownerId) as $item) {
                if ((int) $item['id'] !== $id
                    && mb_strtolower($item['ingredient_name']) === mb_strtolower($clean['ingredient_name'])) {
                    $this->fail(['ingredient_name' => 'This ingredient is already in the pantry. Update its quantity instead.']);
                }
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

    /** MySQL returns DECIMAL as text; the API should return a real number. */
    private function normalize(array $row): array
    {
        if (isset($row['quantity'])) {
            $row['quantity'] = (float) $row['quantity'];
        }
        return $row;
    }
}
