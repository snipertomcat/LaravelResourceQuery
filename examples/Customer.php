<?php

use ResourceQuery\Query\Sort;
use ResourceQuery\Query\Filter;
use ResourceQuery\Query\Relation;
use ResourceQuery\Query\QueryDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use ResourceQuery\Traits\ScopesQueryDefinition;

class Customer extends Model
{
    use ScopesQueryDefinition;
    
    /**
     * Scopes the selected fields on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $fields
     *
     * @return void
     */
    public function scopeFields(Builder $builder, Collection $fields)
    {
        $fields->each(function ($field) use ($builder) {
            $builder->addSelect($field);
        });
    }

    /**
     * Scopes the filters on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $filters
     *
     * @return void
     */
    public function scopeFilters(Builder $builder, Collection $filters)
    {
        $filters->each(function (Filter $filter) use ($builder) {
            $builder->where($filter->name, $filter->operator, $filter->value);
        });
    }

    /**
     * Scopes the sorting of fields on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeSorts(Builder $builder, Collection $sorts)
    {
        $sorts->each(function (Sort $sort) use ($builder) {
            $builder->orderBy($sort->name, $sort->order);
        });
    }

    /**
     * Scopes the eager loading and querying of relationships on the database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeIncludes(Builder $builder, Collection $includes)
    {
        $includes->each(function (Relation $relation) use ($builder) {
            $builder->with([$relation->name => function ($builder) use ($relation) {
                $this->scopeSelectFields($builder, $relation->query->fields);
                $this->scopeApplyFilters($builder, $relation->query->filters);
                $this->scopeApplySorts($builder, $relation->query->sorts);
            }]);
        });
    }
}
