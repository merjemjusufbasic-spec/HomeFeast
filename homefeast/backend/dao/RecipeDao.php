<?php
require_once __DIR__ . '/BaseDao.php';

class RecipeDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('recipes', [
            'title', 'description', 'instructions', 'prep_time', 'category_id', 'user_id'
        ]);
    }

    public function getByCategory(int $categoryId): array
    {
        return $this->query('SELECT * FROM recipes WHERE category_id = :cid', ['cid' => $categoryId]);
    }

    public function getByUser(int $userId): array
    {
        return $this->query('SELECT * FROM recipes WHERE user_id = :uid', ['uid' => $userId]);
    }

    public function searchByTitle(string $term): array
    {
        return $this->query('SELECT * FROM recipes WHERE title LIKE :term', ['term' => '%' . $term . '%']);
    }

    /** Recipes joined with their category name, for list pages. */
    public function getAllWithCategory(): array
    {
        return $this->query(
            'SELECT r.*, c.name AS category_name
             FROM recipes r
             LEFT JOIN categories c ON c.id = r.category_id
             ORDER BY r.created_at DESC'
        );
    }
}
