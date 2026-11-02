<?php
require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories', ['name', 'description']);
    }

    public function getByName(string $name): ?array
    {
        return $this->queryOne('SELECT * FROM categories WHERE name = :name', ['name' => $name]);
    }
}
