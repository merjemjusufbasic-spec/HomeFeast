<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';

class CategoryService extends BaseService
{
    protected string $entityName = 'Category';
    protected array $rules = [
        'name'        => ['type' => 'string', 'required' => true, 'max' => 100],
        'description' => ['type' => 'string', 'nullable' => true, 'max' => 2000],
    ];

    private CategoryDao $categoryDao;

    public function __construct()
    {
        $this->categoryDao = new CategoryDao();
        parent::__construct($this->categoryDao);
    }

    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        $clean = parent::validate($data, $isUpdate, $id);

        // category names must be unique
        if (isset($clean['name'])) {
            $existing = $this->categoryDao->getByName($clean['name']);
            if ($existing !== null && (int) $existing['id'] !== $id) {
                $this->fail(['name' => 'A category with this name already exists.']);
            }
        }
        return $clean;
    }
}
