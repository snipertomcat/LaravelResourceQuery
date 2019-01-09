<?php

namespace ResourceQuery\Query;

use ResourceQuery\Query\Transform;

class ParameterBag
{
    /**
     * The fields in the parameter bag.
     *
     * @param \Illuminate\Support\Collection
     */
    public $fields;

    /**
     * The filters in the parameter bag.
     *
     * @param \Illuminate\Support\Collection
     */
    public $filters;

    /**
     * The sorts in the parameter bag.
     *
     * @param \Illuminate\Support\Collection
     */
    public $sorts;

    /**
     * The includes in the parameter bag.
     *
     * @param \Illuminate\Support\Collection
     */
    public $includes;

    /**
     * The page number.
     *
     * @var integer
     */
    public $page;

    /**
     * The limit per page.
     *
     * @var integer
     */
    public $limit;

    /**
     * Constructs the parameter bag from fields, filters, sorts and includes.
     *
     * @param array $fields    The query fields.
     * @param array $filters   The query filters.
     * @param array $sorts     The query sorts.
     * @param array $includes  The query includes.
     * @param integer $page    The query page.
     * @param integer $limit   The query limit.
     *
     * @return void
     */
    public function __construct(array $fields = [], array $filters = [], array $sorts = [], array $includes = [], $page = 1, $limit = 15)
    {
        $this->setFields($fields);
        $this->setFilters($filters);
        $this->setSorts($sorts);
        $this->setIncludes($includes);
        $this->page = $page;
        $this->limit = $limit;
    }

    /**
     * Return field queries from the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Return filter queries from the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Return sort queries from the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSorts()
    {
        return $this->sorts;
    }

    /**
     * Return relation queries from the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * Set the fields on the adapter.
     *
     * @param array $fields The fields to set.
     *
     * @return void
     */
    public function setFields(array $fields = [])
    {
        $this->fields = Transform::fields($fields);
    }

    /**
     * Set the filters on the adapter.
     *
     * @param array $filters The filters to set.
     *
     * @return void
     */
    public function setFilters(array $filters = [])
    {
        $this->filters = Transform::filters($filters);
    }

    /**
     * Set the sorts on the adapter.
     *
     * @param array $sorts The sorts to set.
     *
     * @return void
     */
    public function setSorts(array $sorts = [])
    {
        $this->sorts = Transform::sorts($sorts);
    }

    /**
     * Set the includes on the adapter.
     *
     * @param array $includes The includes to set.
     *
     * @return void
     */
    public function setIncludes(array $includes = [])
    {
        $this->includes = Transform::includes($includes);
    }
}
