<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users', ['name', 'email', 'password', 'role']);
    }

    public function getByEmail(string $email): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE email = :email', ['email' => $email]);
    }

    /** Same as getAll() but never returns password hashes. */
    public function getAllPublic(): array
    {
        return $this->query('SELECT id, name, email, role, created_at FROM users');
    }
}
