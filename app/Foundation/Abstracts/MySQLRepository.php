<?php

namespace App\Foundation\Abstracts;

use App\Application\Exceptions\MySQLRepositoryException;
use App\Foundation\Interfaces\MySQLRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class MySQLRepository implements MySQLRepositoryInterface
{
    protected Model $model;

    abstract public function getModelName(): string;

    public function __construct()
    {
        $this->makeModel();
    }

    public function makeModel(): void
    {
        $model = app($this->getModelName());

        if (!$model instanceof Model) {
            throw new \Exception("Class $model must be an instance of Illuminate\Database\Eloquent\Model");
        }

        $this->model = $model;
    }

    public function create(array $data = []): Model
    {
        return $this->model->create($data);
    }

    public function getFillableFields(): array
    {
        return $this->model->getFillable();
    }

    public function getCollection(array $columns = ['*']): Collection
    {
        return $this->model::get($columns);
    }

    public function getCollectionByIds(array $ids, array $columns = ['*']): Collection
    {
        return $this->model::findMany($ids, $columns);
    }

    public function update(int $id, string $attribute = 'id', array $data = []): bool
    {
        $model = $this->model::where($attribute, '=', $id)->first();

        if (!$model) {
            return false;
        }

        $model->fill($data);

        return $model->save();
    }

    public function updateMany(array $ids, array $data): int
    {
        return $this->model::whereIn('id', $ids)->update($data);
    }

    public function delete($ids): int
    {
        $ids = Arr::wrap($ids);

        //TODO: make soft Delete
        return $this->model::destroy($ids);
    }

    public function findById(int $id, array $columns = ['*'], array $with = []): Model
    {
        return $this->model::with($with)->findOrFail($id, $columns);
    }

    public function findOneByAttribute(string $attribute, string $value, array $columns = ['*']): Model
    {
        return $this->model::where($attribute, '=', $value)->firstOrFail($columns);
    }

    public function getOneByAttribute(string $attribute, string $value, array $columns = ['*'], array $with = []): ?Model
    {
        return $this->model::with($with)->where($attribute, '=', $value)->first($columns);
    }

    public function getCollectionWhereIn(string $attribute, array $values, array $columns = ['*']): Collection
    {
        return $this->model::whereIn($attribute, $values)->get($columns);
    }

    public function getCollectionWhereBetween(string $attribute, array $values, array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->whereBetween($attribute, $values)->get($columns);
    }

    public function count(): int
    {
        return $this->model::count();
    }

    public function load(int $id, $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function loadRelations(Model $model, array $relations): void
    {
        $model->load($relations);
    }

    protected function exception(string $message, int $code = 0): void
    {
        throw new MySQLRepositoryException($message, $code);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return \call_user_func_array([$this->model, $name], $arguments);
    }
}