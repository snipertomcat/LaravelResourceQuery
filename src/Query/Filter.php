<?php

namespace ResourceQuery\Query;

use Carbon\Carbon;
use InvalidArgumentException;

class Filter
{
    /**
     * The field to filter on.
     *
     * @var string
     */
    public $name;

    /**
     * The value to filter by.
     *
     * @var string
     */
    public $value;

    /**
     * The operator to filter with.
     *
     * @var string
     */
    public $operator;

    /**
     * Attempt to transform dates into Carbon instances.
     *
     * @var boolean
     */
    protected $transformDates = false;

    /**
     * The format when handling date times.
     *
     * @var string
     */
    protected $datetimeFormat = 'U';

    /**
     * The mapping between a filter type and the operator.
     *
     * @var array
     */
    protected $typeOperatorMap = [
        'min' => '>=',
        'max' => '<=',
        'not' => '!=',
    ];
    
    /**
     * The constructor for the Filter.
     * @param string $name
     * @param string $value
     * @param string $operator
     */
    public function __construct($name, $value, $type = null)
    {
        $this->name = $name;
        $this->value = $this->parseValue($value, $type);
        $this->operator = $this->parseOperator($value, $type);
    }

    /**
     * Parse the value of the filter. This includes finding `is` and `not`,
     * `min` and `max`, and a datetime format.
     * @param  mixed $value
     * @return mixed
     */
    public function parseValue($value, $type)
    {
        if ($value === null || $value === 'null') {
            $value = null;
        }

        if ($value === null && $type !== null) {
            throw InvalidArgumentException('Value cannot be empty or null when filtering by a type such as min / max.');
        }

        if ($this->transformDates && $date = $this->parseDate($value)) {
            return $date;
        }

        if ($list = $this->parseValueList($value)) {
            return $list;
        }

        return $value;
    }

    /**
     * Parse the filter value for a list of values.
     *
     * @param  string|null $value
     *
     * @return array|null
     */
    public function parseValueList($value)
    {
        if ($value === null) {
            return null;
        }
        
        if (strpos($value, ',') === false) {
            return null;
        }

        return explode(',', $value);
    }

    /**
     * Attempt to parse a date value. Will return either a Carbon instance or null.
     *
     * @param  string $potentialDate
     *
     * @return \Carbon\Carbon|null
     */
    public function parseDate($potentialDate)
    {
        if ($potentialDate === null) {
            return null;
        }

        try {
            return Carbon::createFromFormat($this->datetimeFormat, $potentialDate);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse the operator of the filter.
     *
     * @param  mixed $filterValue
     * @param  string|null $type
     *
     * @return string
     */
    public function parseOperator($filterValue, $type)
    {
        if ($filterValue === null) {
            return '!=';
        }

        if ($type === null) {
            return '=';
        }

        if (!array_key_exists($type, $this->typeOperatorMap)) {
            throw InvalidArgumentException('Filter type not supported: ' . $type);
        }

        return $this->typeOperatorMap[$type];
    }
}
