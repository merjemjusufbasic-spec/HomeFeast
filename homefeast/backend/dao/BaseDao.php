<?php
require_once __DIR__ . '/Database.php';

/**
 * Reusable CRUD for every DAO. Child classes only pass the table name
 * (and, if needed, the list of columns that may be written).
 *
 * Table and column names never come from user input: they come from the
 * child class, and column names are checked against $allowedColumns.
 * All values are bound with prepared statements.
 */
abstract class BaseDao
{
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';
    /** @var string[] columns that insert()/update() are allowed to write */
    protected array $allowedColumns = [];

    public function __construct(string $table, array $allowedColumns)
    {
        $this->connection     = Database::connect();
        $this->table          = $table;
        $this->allowedColumns = $allowedColumns;
    }

    /** Run a prepared SELECT and return all rows. */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a prepared SELECT and return one row, or null. */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    /** READ (all) */
    public function getAll(): array
    {
        return $this->query("SELECT * FROM {$this->table}");
    }

    /** READ (one) */
    public function getById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
    }

    /** CREATE - returns the new row id */
    public function insert(array $data): int
    {
        $data = $this->filterColumns($data);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid columns to insert.');
        }
        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)"
        );
        $stmt->execute($data);
        return (int) $this->connection->lastInsertId();
    }

    /** UPDATE - returns true if a row was changed */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterColumns($data);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid columns to update.');
        }
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));

        $data['__id'] = $id;
        $stmt = $this->connection->prepare(
            "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :__id"
        );
        $stmt->execute($data);
        return $stmt->rowCount() > 0;
    }

    /** DELETE - returns true if a row was removed */
    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Keep only columns the child class allows. */
    private function filterColumns(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedColumns));
    }
}
