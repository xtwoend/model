<?php

namespace Xtwoend\Model;

use Xtwoend\Model\Model;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Database\Query\Builder as QueryBuilder;

class Builder
{
    protected $query;
    protected $model;
    protected $passthru = [
        'toSql', 'lists', 'insert', 'insertGetId', 'pluck',
        'count', 'min', 'max', 'avg', 'sum', 'exists',
    ];

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function __call($method, $parameters)
    {
        $result = call([$this->query, $method], $parameters);
        return in_array($method, $this->passthru) ? $result : $this;
    }

    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        $this->where($this->model->getKeyName(), '=', $id);

        return $this->first($columns);
    }

    public function findMany($id, $columns = ['*'])
    {
        $this->query->whereIn($this->model->getKeyName(), $id);
        return $this->get($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->query->where(...func_get_args());
        return $this;
    }

    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    public function get($columns = ['*'])
    {
        $builder = clone $this;
        $models = $builder->getModels($columns);
        return $builder->getModel()->newCollection($models);
    }

    public function all($columns = ['*'])
    {
        return $this->get($columns);
    }

    public function paginate(?int $perPage = null, ?int $page = 1, array $columns = ['*'])
    {
        $perPage = $perPage ?: $this->model->getPerPage();

        $results = ($total = $this->query->getCountForPagination())
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return (object) [
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage
            ],
            'data' => $results
        ];
    }

    public function infinite(?int $perPage = null, ?int $lastId = 0, array $columns = ['*'])
    {
        $perPage = $perPage ?: $this->model->getPerPage();

        $results = ($total = $this->query->getCountForPagination())
            ? $this->query->where($this->model->getKeyName(), '>', $lastId)
                ->limit($perPage)
                ->get($columns)
            : $this->model->newCollection();

        return [
            'meta' => [
                'per_page' => $perPage,
                'total' => $total
            ],
            'data' => $results
        ];
    }

    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();
        return $instance->newCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));
    }

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }
}