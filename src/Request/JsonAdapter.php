<?php

namespace ResourceQuery\Request;

use Illuminate\Http\Request;
use ResourceQuery\Contracts\Adapter;

class JsonAdapter implements Adapter
{
    /**
     * Map the given array of query parameters.
     *
     * @param array $query
     * @return array
     */
    public function map(array $query = [])
    {
        return [
            'fields' => $this->mapFields($query['fields'] ?? ''),
            'filters' => $this->mapFilters($query['filters'] ?? []),
            'sorts' => $this->mapSorts($query['sorts'] ?? []),
            'includes' => $this->mapIncludes($query['includes'] ?? []),
            'limit' => $query['limit'] ?? 15,
            'page' => $query['page'] ?? 1,
        ];
    }

    /**
     * Map the query fields.
     *
     * @param string $fields
     *
     * @return array
     */
    public function mapFields($fields = [])
    {
        if (is_array($fields)) {
            return $fields;
        }

        $fields = empty($fields) ? [] : explode(',', $fields);

        $result = [];

        foreach ($fields as $field) {
            $result[$field] = true;
        }

        return $result;
    }

    /**
     * Map the query filters.
     *
     * @param array $filters
     *
     * @return array
     */
    public function mapFilters(array $filters = [])
    {
        $result = [];

        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Map the query sorts.
     *
     * @param array $sorts
     *
     * @return array
     */
    public function mapSorts(array $sorts = [])
    {
        return $sorts;
    }

    /**
     * Map the query includes.
     *
     * @param array $includes
     *
     * @return array
     */
    public function mapIncludes(array $includes = [])
    {
        $result = [];

        foreach ($includes as $key => $value) {
            $result[$key] = [
                'fields' => $this->mapFields($value['fields'] ?? ''),
                'filters' => $this->mapFilters($value['filters'] ?? []),
                'sorts' => $this->mapSorts($value['sorts'] ?? []),
            ];
        }

        return $result;
    }
}
