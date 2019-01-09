<?php

namespace ResourceQuery\Query;

use Illuminate\Http\Request;
use ResourceQuery\Contracts\Adapter;
use ResourceQuery\Query\ParameterBag;
use Illuminate\Contracts\Container\Container;

abstract class QueryDefinition
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * The adapter instance.
     *
     * @var \ResourceQuery\Contracts\Adapter
     */
    public $adapter;

    /**
     * The parameters on the query.
     *
     * @var \ResourceQuery\Query\ParameterBag
     */
    protected $parameters;

    /**
     * The fields that can be queried against.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * The includes that can be eager loaded and queried against.
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Transformations on the names of fields and includes.
     *
     * @var array
     */
    protected $transform = [];

    /**
     * The constructor for the class.
     *
     * @param \Illuminate\Http\Request $request
     * @param \ResourceQuery\Contracts\Adapter $adapter
     */
    public function __construct(Request $request, Adapter $adapter)
    {
        $this->request = $request;
        $this->adapter = $adapter;
    }

    /**
     * Set the fields property.
     *
     * @param array $fields
     * @return void
     */
    public function setFields(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * Set the includes property.
     *
     * @param array $fields
     * @return void
     */
    public function setIncludes(array $includes = [])
    {
        $this->includes = $includes;
    }

    /**
     * Set the transform property.
     *
     * @param array $transform
     * @return void
     */
    public function setTransform(array $transform = [])
    {
        $this->transform = $transform;
    }

    /**
     * Set the query parameters.
     *
     * @var array $parameters
     * @var boolean $rootQuery
     *
     * @return self
     */
    public function setParameters(array $parameters = [], $rootQuery = true)
    {
        $query = $this->adapter->map($parameters);

        $this->parameters = $this->newParameterBag(
            $query['fields'] ?? [],
            $query['filters'] ?? [],
            $query['sorts'] ?? [],
            $rootQuery ? $query['includes'] ?? [] : [],
            $rootQuery ? $query['page'] ?? 1 : null,
            $rootQuery ? $query['limit'] ?? 15 : null
        );

        return $this;
    }
    
    /**
     * Build a new query bag from the given request query parameters.
     *
     * @param array $fields
     * @param array $filters
     * @param array $sorts
     * @param array $includes
     *
     * @return \ResourceQuery\Query\ParameterBag
     */
    public function newParameterBag($fields = [], $filters = [], $sorts = [], $includes = [], $page = 1, $limit = 15)
    {
        return new ParameterBag(
            $this->normalizeParameters($fields),
            $this->normalizeParameters($filters),
            $this->normalizeParameters($sorts),
            $this->normalizeIncludes($includes),
            $page,
            $limit
        );
    }

    /**
     * Return an array of the defined fields filtered and transformed.
     *
     * @param array $parameters
     *
     * @return array
     */
    public function normalizeParameters($parameters = [])
    {
        $definedParameters = $this->filterDefinedParameters($parameters);
        $authorizedParameters = $this->filterAuthorizedParameters($definedParameters);
        $transformedParameters = $this->transformParameters($authorizedParameters);

        return $transformedParameters->toArray();
    }

    /**
     * Return an array of the defined fields filtered and transformed.
     *
     * @param array $includes
     *
     * @return array
     */
    public function normalizeIncludes(array $includes = [])
    {
        $definedIncludes = $this->filterDefinedIncludes($includes);
        $authorizedIncludes = $this->filterAuthorizedIncludes($definedIncludes);
        $initializedIncludes = $this->initializeIncludes($authorizedIncludes);
        $transformedIncludes = $this->transformIncludes($initializedIncludes);

        return $transformedIncludes->toArray();
    }

    /**
     * Map the includes to their corresponding bag of query parameters.
     *
     * @param array $includes
     *
     * @return array
     */
    private function initializeIncludes($includes)
    {
        $result = [];

        foreach ($includes as $key => $value) {
            $result[$key] = $this->makeInclude($key)->setParameters($value, false);
        }

        return collect($result);
    }

    /**
     * Make the given include key into it's corresponding query class.
     *
     * @param string $key
     *
     * @return \ResourceQuery\Query\QueryDefinition
     */
    private function makeInclude(string $key)
    {
        return new $this->includes[$key]($this->request, $this->adapter);
    }

    /**
     * Map the given parameters through the transformer
     *
     * @param array $parameters
     *
     * @return \Illuminate\Support\Collection
     */
    private function transformParameters($parameters = [])
    {
        $result = [];

        foreach ($parameters as $key => $value) {
            $result[$this->transform[$key] ?? $key] = $value;
        }

        return collect($result);
    }

    /**
     * Map the given includes through the transformer
     *
     * @param array $includes
     *
     * @return \Illuminate\Support\Collection
     */
    private function transformIncludes($includes = [])
    {
        $result = [];

        foreach ($includes as $key => $value) {
            $result[$this->transform[$key] ?? $key] = $value;
        }

        return collect($result);
    }

    /**
     * Filter the request parameters by the given definition of acceptable fields.
     *
     * @param array $parameters
     *
     * @return \Illuminate\Support\Collection
     */
    private function filterDefinedParameters($parameters)
    {
        return collect($parameters)->filter(function ($value, $key) {
            return in_array($key, $this->fields);
        });
    }

    /**
     * Filter the request includes by the given definition of acceptable includes.
     *
     * @param array $includes
     *
     * @return \Illuminate\Support\Collection
     */
    private function filterDefinedIncludes($includes = [])
    {
        return collect($includes)->filter(function ($value, $key) {
            return array_key_exists($key, $this->includes);
        });
    }

    /**
     * Filter fields by those that are authorized on the class definition. Checks
     * for a defined authorizer method on the class, and reduces the truthiness
     * of the method call.
     *
     * @param array $fields
     *
     * @return \Illuminate\Support\Collection
     */
    private function filterAuthorizedParameters($fields = [])
    {
        return collect($fields)->filter(function ($value, $key) {
            return $this->isAuthorized($key, 'Field');
        });
    }

    /**
     * Filter includes by those that are authorized on the class definition. Checks
     * for a defined authorizer method on the class, and reduces the truthiness
     * of the method call.
     *
     * @param array $includes
     *
     * @return \Illuminate\Support\Collection
     */
    private function filterAuthorizedIncludes($includes = [])
    {
        return collect($includes)->filter(function ($class, $include) {
            return $this->isAuthorized($include, 'Include');
        });
    }

    /**
     * Determine if the given field or include name is authorized to be queried.
     * If there is no authorizer method defined on the class, the field or include
     * is assumed to be authorized.
     *
     * @param string $name
     * @param string $type
     *
     * @return bool
     */
    private function isAuthorized(string $name, string $type)
    {
        $authorizer = $this->getAuthorizer($name, $type);

        if (!$authorizer) {
            return true;
        }

        return $this->$authorizer($this->request);
    }

    /**
     * Returns the name of the authorizer method defined on the current class. If
     * no definition is found, returns null.
     *
     * @param string $name
     * @param string $type
     *
     * @return string|null
     */
    private function getAuthorizer(string $name, string $type)
    {
        $upperCasedName = ucwords($name, '_');

        $camelCasedName = str_replace('_', '', $upperCasedName);

        $authorizer = 'authorize' . $camelCasedName . $type;

        return method_exists($this, $authorizer) ? $authorizer : null;
    }

    /**
     * Determines if the given array of values contains true, when each
     * value is cast to a boolean.
     *
     * @param array $authorizeChecks
     *
     * @return bool
     */
    protected function allow(array $authorizeChecks)
    {
        return collect($authorizeChecks)->map(function ($value) {
            return (bool) $value;
        })->filter()->isNotEmpty();
    }

    /**
     * Determines if the given array of values contains true, when each
     * value is cast to a boolean.
     *
     * @param array $authorizeChecks
     *
     * @return bool
     */
    protected function deny(array $authorizeChecks)
    {
        return !$this->allow($authorizeChecks);
    }

    /**
     * Return a parameter from the parameter bag.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getParameter($key)
    {
        return $this->parameters->$key;
    }

    /**
     * Dynamically retrieve parameters on the query.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getParameter($key);
    }
}
