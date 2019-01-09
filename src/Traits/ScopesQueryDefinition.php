<?php

namespace ResourceQuery\Traits;

use ResourceQuery\Query\Sort;
use ResourceQuery\Query\Filter;
use Illuminate\Support\Collection;
use ResourceQuery\Query\QueryDefinition;

trait ScopesQueryDefinition
{
    /**
     * Scopes the selected fields on a database query builder.
     *
     * @param $builder
     * @param \Illuminate\Support\Collection $fields
     *
     * @return void
     */
    public function scopeFields($builder, Collection $fields)
    {
        if ($fields->isNotEmpty()) {
            $builder->addSelect('id');
        }

        $fields->each(function ($field) use ($builder) {
            $builder->addSelect($field);
        });
    }

    /**
     * Scopes the filters on a database query builder.
     *
     * @param $builder
     * @param \Illuminate\Support\Collection $filters
     *
     * @return void
     */
    public function scopeFilters($builder, Collection $filters)
    {
        $filters->each(function (Filter $filter) use ($builder) {
            $builder->where($filter->name, $filter->operator, $filter->value);
        });
    }

    /**
     * Scopes the sorting of fields on a database query builder.
     *
     * @param $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeSorts($builder, Collection $sorts)
    {
        $sorts->each(function (Sort $sort) use ($builder) {
            $builder->orderBy($sort->name, $sort->order);
        });
    }

    /**
     * Scopes the eager loading and querying of relationships on the database query builder.
     *
     * @param $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeIncludes($builder, Collection $includes)
    {
        $includes->each(function ($relation) use ($builder) {
            $builder->with([$relation->name => function ($builder) use ($relation) {
                $this->scopeFields($builder, $relation->query->fields);
                $this->scopeFilters($builder, $relation->query->filters);
                $this->scopeSorts($builder, $relation->query->sorts);
            }]);
        });
    }

    /**
     * Scopes the database query builder from a query resource.
     *
     * @param $builder
     * @param \ResourceQuery\Query\QueryDefinition $query
     *
     * @return void
     */
    public function scopeFromQuery($builder, QueryDefinition $query)
    {
        $builder->fields($query->fields)
                ->filters($query->filters)
                ->sorts($query->sorts)
                ->includes($query->includes);
    }
}
