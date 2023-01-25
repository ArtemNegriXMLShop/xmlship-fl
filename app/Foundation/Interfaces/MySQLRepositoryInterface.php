<?php

namespace App\Foundation\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface MySQLRepositoryInterface
{
    /**
     * Create a model by its Class name.
     *
     * @throws \Exception
     * @return void
     */
    public function makeModel(): void;

    public function create(array $data = []): Model;

    public function getCollection(array $columns = ['*']): Collection;

    public function getCollectionByIds(array $ids, array $columns = ['*']): Collection;

    public function update(int $id, string $attribute = 'id', array $data = []): bool;

    public function updateMany(array $ids, array $data): int;

    public function delete($ids): int;

    public function findById(int $id, array $columns = ['*'], array $with = []): Model;

    public function findOneByAttribute(string $attribute, string $value, array $columns = ['*']): Model;
    public function getOneByAttribute(string $attribute, string $value, array $columns = ['*']): ?Model;

    public function getCollectionWhereIn(string $attribute, array $values, array $columns = ['*']): Collection;

    public function getCollectionWhereBetween(string $attribute, array $values, array $columns = ['*']): Collection;

    public function count(): int;

    public function load(int $id, array $columns = ['*']): ?Model;

    public function loadRelations(Model $model, array $relations): void;

    public function __call(string $name, array $arguments): mixed;
}