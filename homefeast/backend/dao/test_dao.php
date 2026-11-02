<?php
// Quick CLI smoke test for the DAO layer. Run from backend/dao:  php test_dao.php
// It inserts, reads, updates and deletes a temporary category and cleans up after itself.
require_once __DIR__ . '/CategoryDao.php';
require_once __DIR__ . '/UserDao.php';
require_once __DIR__ . '/RecipeDao.php';
require_once __DIR__ . '/RecipeIngredientDao.php';
require_once __DIR__ . '/PantryDao.php';
require_once __DIR__ . '/FavoriteDao.php';

$categories = new CategoryDao();
$id = $categories->insert(['name' => 'Temp Category', 'description' => 'DAO test']);
echo "CREATE  id=$id\n";
echo "READ    " . json_encode($categories->getById($id)) . "\n";
$categories->update($id, ['description' => 'Updated']);
echo "UPDATE  " . json_encode($categories->getById($id)) . "\n";
$categories->delete($id);
echo "DELETE  " . var_export($categories->getById($id), true) . "\n\n";

echo 'users:              ' . count((new UserDao())->getAllPublic()) . "\n";
echo 'categories:         ' . count($categories->getAll()) . "\n";
echo 'recipes:            ' . count((new RecipeDao())->getAll()) . "\n";
echo 'recipe_ingredients: ' . count((new RecipeIngredientDao())->getAll()) . "\n";
echo 'pantry:             ' . count((new PantryDao())->getAll()) . "\n";
echo 'favorites:          ' . count((new FavoriteDao())->getAll()) . "\n";
echo 'what can user 2 cook: ' . json_encode((new PantryDao())->getRecipeMatches(2)) . "\n";
