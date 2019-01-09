<?php

namespace ResourceQuery\Query;

class Transform
{
    /**
     * Transform request filters into a collection.
     *
     * @param array $fields
     *
     * @return \Illuminate\Support\Collection
     */
    public static function fields($fields = [])
    {
        return collect($fields)->map(function ($value, $key) {
            return $key;
        })->flatten();
    }

    /**
     * Transform request filters into a collection.
     *
     * @param array $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public static function filters($filters = [])
    {
        return collect($filters)->map(function ($filter, $name) {
            if (!is_array($filter)) {
                return new Filter($name, $filter);
            }

            return collect($filter)->map(function ($value, $type) use ($name) {
                return new Filter($name, $value, $type);
            });
        })->flatten();
    }

    /**
     * Transform request sorts into collection
     *
     * @param  array $sorts
     *
     * @return \Illuminate\Support\Collection
     */
    public static function sorts($sorts = [])
    {
        return collect($sorts)->map(function ($order, $name) {
            return new Sort($name, $order);
        })->flatten();
    }

    /**
     * Transform request includes into a Collection
     *
     * @param  array $includes
     *
     * @return \Illuminate\Support\Collection
     */
    public static function includes($includes = [])
    {
        return collect($includes)->map(function (QueryDefinition $query, $name) {
            return new Relation($name, $query);
        })->flatten();
    }
}
