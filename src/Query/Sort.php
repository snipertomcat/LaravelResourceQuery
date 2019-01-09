<?php

namespace ResourceQuery\Query;

class Sort
{
    /**
     * The field to sort on.
     * @var string
     */
    public $name;

    /**
     * The order to sort by.
     * @var string
     */
    public $order;
    
    /**
     * The constructor for the Sort.
     * @param string $name
     * @param string $order
     */
    public function __construct($name, $order)
    {
        $this->name = $name;
        $this->order = $order ?? 'asc';
    }
}
