<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\Model;
use PDO;

abstract class Repository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

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

    protected function getTable(): string
    {
        $modelClass = $this->getModelClass();
        $parts = \explode('\\', $modelClass);
        $shortName = \end($parts);

        // Expression régulière : on cherche une minuscule ([a-z]) suivie d'une majuscule ([A-Z])
        // On remplace par "minuscule_majuscule"
        $snakeCase = \strtolower(\preg_replace('/([a-z])([A-Z])/', '$1_$2', $shortName));

        return $snakeCase . 's';
    }

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

    final public function save(Model $model): bool
    {
        if (!empty($model->id)) {
            return $this->update($model);
        }

        return $this->insert($model);
    }

    private function formatData(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        return $data;
    }

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
        $set = \implode(', ', \array_map(fn(string $col) => "$col = :$col", \array_keys($formattedData)));

        $stmt = $this->pdo->prepare("UPDATE {$this->getTable()} SET $set WHERE id = :id");
        $formattedData['id'] = $id;

        return $stmt->execute($formattedData);
    }

    final public function delete(Model $model): bool
    {
        if (empty($model->id)) {
            throw new \Exception('Cannot delete a model without ID');
        }

        $stmt = $this->pdo->prepare("DELETE FROM {$this->getTable()} WHERE id = :id");
        return $stmt->execute(['id' => $model->id]);
    }
}
