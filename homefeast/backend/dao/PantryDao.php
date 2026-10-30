<?php
require_once __DIR__ . '/BaseDao.php';

class PantryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('pantry', ['user_id', 'ingredient_name', 'quantity', 'unit']);
    }

    public function getByUser(int $userId): array
    {
        return $this->query(
            'SELECT * FROM pantry WHERE user_id = :uid ORDER BY ingredient_name',
            ['uid' => $userId]
        );
    }

    /**
     * "What can I cook?" - recipes ranked by how many of their ingredients
     * the user already has in the pantry (case-insensitive name match).
     */
    public function getRecipeMatches(int $userId): array
    {
        return $this->query(
            'SELECT r.id, r.title, r.description,
                    COUNT(ri.id) AS total_ingredients,
                    SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) AS matched_ingredients
             FROM recipes r
             JOIN recipe_ingredients ri ON ri.recipe_id = r.id
             LEFT JOIN pantry p
                    ON p.user_id = :uid
                   AND LOWER(p.ingredient_name) = LOWER(ri.ingredient_name)
             GROUP BY r.id, r.title, r.description
             HAVING matched_ingredients > 0
             ORDER BY matched_ingredients DESC',
            ['uid' => $userId]
        );
    }
}
