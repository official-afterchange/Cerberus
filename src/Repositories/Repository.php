<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\Model;
use PDO;

/**
 * Base repository providing generic CRUD operations with reflection-based table and model resolution.
 */
abstract class Repository
{
    protected PDO $pdo;

    /**
     * Initializes the repository with a database connection.
     *
     * @param PDO $pdo The active PDO database connection.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Resolves the fully qualified model class name from the concrete repository class name.
     *
     * @return string The fully qualified model class name.
     * @throws \Exception If no matching model class is found.
     */
    protected function getModelClass(): string
    {
        $repoClass = new \ReflectionClass($this);
        $repoName = $repoClass->getShortName();

        $modelName = \str_replace('Repository', '', $repoName);
        $modelClass = "Afterchange\\Template\\Models\\$modelName";

        if (!\class_exists($modelClass)) {
            throw new \Exception("Model $modelClass not found for $repoName");
        }

        return $modelClass;
    }

    /**
     * Derives the database table name from the model class name using snake_case conversion.
     *
     * @return string The pluralized snake_case table name (e.g., 'oauth_clients').
     */
    protected function getTable(): string
    {
        $modelClass = $this->getModelClass();
        $parts = \explode('\\', $modelClass);
        $shortName = \end($parts);

        $snakeCase = \strtolower(\preg_replace('/([a-z])([A-Z])/', '$1_$2', $shortName));

        return $snakeCase . 's';
    }

    /**
     * Finds a single model by its primary key.
     *
     * @param int $id The record ID to look up.
     * @return Model|null The hydrated model, or null if not found.
     */
    final public function find(int $id): ?Model
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTable()} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $modelClass = $this->getModelClass();
        $model = new $modelClass();
        $model->hydrate($data);

        return $model;
    }

    /**
     * Returns all records from the table as hydrated model instances.
     *
     * @return Model[] An array of hydrated model instances.
     */
    final public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->getTable()}");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = [];
        $modelClass = $this->getModelClass();

        foreach ($rows as $row) {
            $model = new $modelClass();
            $model->hydrate($row);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Persists a model by inserting it if new, or updating it if it already has an ID.
     *
     * @param Model $model The model instance to persist.
     * @return bool        True on success, false on failure.
     */
    final public function save(Model $model): bool
    {
        if (!empty($model->id)) {
            return $this->update($model);
        }

        return $this->insert($model);
    }

    /**
     * Converts DateTime instances in a data array to formatted date strings for database storage.
     *
     * @param array $data The raw key-value data to format.
     * @return array      The formatted data array with DateTime values replaced by strings.
     */
    private function formatData(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        return $data;
    }

    /**
     * Inserts a new model record into the database and assigns the generated ID back to the model.
     *
     * @param Model $model The model instance to insert.
     * @return bool        True on success, false on failure.
     */
    private function insert(Model $model): bool
    {
        $fillable = $model->getFillable();
        $data = \array_intersect_key(\get_object_vars($model), \array_flip($fillable));

        if (isset($data['id'])) {
            unset($data['id']);
        }

        $formattedData = $this->formatData($data);
        $columns = \implode(', ', \array_keys($formattedData));
        $placeholders = ':' . \implode(', :', \array_keys($formattedData));

        $stmt = $this->pdo->prepare("INSERT INTO {$this->getTable()} ($columns) VALUES ($placeholders)");
        $res = $stmt->execute($formattedData);

        if ($res && \property_exists($model, 'id') && empty($model->id)) {
            $model->id = (int) $this->pdo->lastInsertId();
        }

        return $res;
    }

    /**
     * Updates an existing model record in the database.
     *
     * @param Model $model The model instance to update (must have a valid ID).
     * @return bool        True on success, false on failure.
     * @throws \Exception  If the model has no ID set.
     */
    private function update(Model $model): bool
    {
        $fillable = $model->getFillable();
        $data = \array_intersect_key(\get_object_vars($model), \array_flip($fillable));

        if (empty($data['id'])) {
            throw new \Exception('Cannot update a model without ID');
        }

        $id = $data['id'];
        unset($data['id']);

        $formattedData = $this->formatData($data);
        $set = \implode(', ', \array_map(fn (string $col) => "$col = :$col", \array_keys($formattedData)));

        $stmt = $this->pdo->prepare("UPDATE {$this->getTable()} SET $set WHERE id = :id");
        $formattedData['id'] = $id;

        return $stmt->execute($formattedData);
    }

    /**
     * Deletes a model record from the database by its primary key.
     *
     * @param Model $model The model instance to delete (must have a valid ID).
     * @return bool        True on success, false on failure.
     * @throws \Exception  If the model has no ID set.
     */
    final public function delete(Model $model): bool
    {
        if (empty($model->id)) {
            throw new \Exception('Cannot delete a model without ID');
        }

        $stmt = $this->pdo->prepare("DELETE FROM {$this->getTable()} WHERE id = :id");
        return $stmt->execute(['id' => $model->id]);
    }
}