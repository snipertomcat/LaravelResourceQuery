<?php

namespace ResourceQuery\Query;

use ResourceQuery\Query\QueryBag;

class Relation
{
    /**
     * The name of the relationship to include.
     * @var string
     */
    public $name;

    /**
     * The queries on the relation.
     * @var \ResourceQuery\Query\QueryDefinition
     */
    public $query;

    /**
     * The constructor for the class.
     * @param string $name
     * @param \ResourceQuery\Query\QueryDefinition $QueryBag
     */
    public function __construct($name, QueryDefinition $query)
    {
        $this->name = $name;
        $this->query = $query;
    }
}
