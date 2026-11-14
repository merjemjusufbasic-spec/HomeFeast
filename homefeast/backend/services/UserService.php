<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    protected string $entityName = 'User';
    protected array $rules = [
        'name'     => ['type' => 'string', 'required' => true, 'max' => 100],
        'email'    => ['type' => 'email',  'required' => true, 'max' => 150],
        // 72 = the most bcrypt will look at
        'password' => ['type' => 'string', 'required' => true, 'min' => 8, 'max' => 72],
        'role'     => ['type' => 'enum', 'values' => ['admin', 'user']],
    ];

    private UserDao $userDao;

    public function __construct()
    {
        $this->userDao = new UserDao();
        parent::__construct($this->userDao);
    }

    /** Never expose password hashes. */
    public function getAll(): array
    {
        return $this->userDao->getAllPublic();
    }

    public function getById(int $id): array
    {
        $user = parent::getById($id);
        unset($user['password']);
        return $user;
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        $clean  = parent::validate($data, $isUpdate, $id);
        $errors = [];

        // email must be unique (ignoring the user we are updating)
        if (isset($clean['email'])) {
            $existing = $this->userDao->getByEmail($clean['email']);
            if ($existing !== null && (int) $existing['id'] !== $id) {
                $errors['email'] = 'This email is already registered.';
            }
        }
        $this->fail($errors);

        if (!$isUpdate && !isset($clean['role'])) {
            $clean['role'] = 'user';
        }
        if (isset($clean['password'])) {
            $clean['password'] = password_hash($clean['password'], PASSWORD_BCRYPT);
        }
        return $clean;
    }

    public function update(int $id, array $data): array
    {
        if (isset($data['role']) && $data['role'] !== 'admin' && $this->isLastAdmin($id)) {
            throw new ApiException('The last admin cannot be demoted.', 400);
        }
        return parent::update($id, $data);
    }

    public function delete(int $id): void
    {
        if ($this->isLastAdmin($id)) {
            throw new ApiException('The last admin cannot be deleted.', 400);
        }
        parent::delete($id);
    }

    /** Business rule: the system must always keep at least one admin. */
    private function isLastAdmin(int $id): bool
    {
        $admins = array_values(array_filter(
            $this->userDao->getAllPublic(),
            fn($u) => $u['role'] === 'admin'
        ));
        return count($admins) === 1 && (int) $admins[0]['id'] === $id;
    }
}
