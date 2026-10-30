<?php
require_once __DIR__ . '/BaseDao.php';

class FavoriteDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('favorites', ['user_id', 'recipe_id']);
    }

    /** A user's favorite recipes (joined with recipe data). */
    public function getByUser(int $userId): array
    {
        return $this->query(
            'SELECT f.id AS favorite_id, f.created_at AS favorited_at, r.*
             FROM favorites f
             JOIN recipes r ON r.id = f.recipe_id
             WHERE f.user_id = :uid
             ORDER BY f.created_at DESC',
            ['uid' => $userId]
        );
    }

    public function exists(int $userId, int $recipeId): bool
    {
        return $this->queryOne(
            'SELECT id FROM favorites WHERE user_id = :uid AND recipe_id = :rid',
            ['uid' => $userId, 'rid' => $recipeId]
        ) !== null;
    }

    public function deleteByUserAndRecipe(int $userId, int $recipeId): bool
    {
        $stmt = $this->connection->prepare(
            'DELETE FROM favorites WHERE user_id = :uid AND recipe_id = :rid'
        );
        $stmt->execute(['uid' => $userId, 'rid' => $recipeId]);
        return $stmt->rowCount() > 0;
    }
}
