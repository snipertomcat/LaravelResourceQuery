<?php

namespace ResourceQuery\Tests\Unit\Query;

use Tests\TestCase;
use RuntimeException;
use Illuminate\Http\Request;
use ResourceQuery\Query\Sort;
use ResourceQuery\Query\Filter;
use ResourceQuery\Query\Relation;
use ResourceQuery\Contracts\Adapter;
use ResourceQuery\Query\QueryDefinition;
use ResourceQuery\Request\JsonAdapter;
use ResourceQuery\Exceptions\ModelNotDefinedException;

class ResourceQueryTest extends TestCase
{
    public function mockQueryParams()
    {
        return [
            'fields' => 'foo,bar,baz',
            'filters' => [
                'foo' => 'bar',
                'bar' => [
                    'min' => 0,
                    'max' => 1,
                ],
                'baz' => 'foo,bar,baz',
            ],
            'sorts' => [
                'foo' => 'asc',
                'bar' => 'desc',
            ],
            'includes' => [
                'one' => '',
                'two' => [
                    'fields' => 'foo,bar,baz',
                    'filters' => [
                        'foo' => 'bar',
                    ],
                    'sorts' => [
                        'foo' => 'asc',
                    ]
                ],
                'three' => [
                    'fields' => '1,2,3',
                ]
            ],
            'page' => 10,
            'limit' => 100,
        ];
    }

    public function testQueryDefinitionParameters()
    {
        $query = app(FooQuery::class);
        $query->setParameters($this->mockQueryParams());

        $fields = $query->fields;
        $filters = $query->filters;
        $sorts = $query->sorts;
        $includes = $query->includes;
        $page = $query->page;
        $limit = $query->limit;

        $this->assertEquals('fooo', $fields[0]);
        $this->assertEquals('bar', $fields[1]);

        $this->assertEquals(10, $page);
        $this->assertEquals(100, $limit);
        
        $filters->each(function ($filter) {
            $this->assertTrue($filter instanceof Filter);
        });

        $this->assertEquals(3, $filters->count());
        $this->assertEquals('fooo', $filters[0]->name);
        $this->assertEquals('=', $filters[0]->operator);
        $this->assertEquals('bar', $filters[0]->value);
        $this->assertEquals('bar', $filters[1]->name);
        $this->assertEquals('>=', $filters[1]->operator);
        $this->assertEquals('0', $filters[1]->value);
        $this->assertEquals('bar', $filters[2]->name);
        $this->assertEquals('<=', $filters[2]->operator);
        $this->assertEquals('1', $filters[2]->value);

        $sorts->each(function ($sort) {
            $this->assertTrue($sort instanceof Sort);
        });

        $this->assertEquals(2, $sorts->count());
        $this->assertEquals('fooo', $sorts[0]->name);
        $this->assertEquals('asc', $sorts[0]->order);
        $this->assertEquals('bar', $sorts[1]->name);
        $this->assertEquals('desc', $sorts[1]->order);

        $includes->each(function ($include) {
            $this->assertTrue($include instanceof Relation);
        });

        $this->assertEquals(2, $includes->count());
        $this->assertEquals('onee', $includes[0]->name);
        $this->assertEquals('two', $includes[1]->name);

        $fields = $includes[1]->query->fields;
        $filters = $includes[1]->query->filters;
        $sorts = $includes[1]->query->sorts;
        $includes = $includes[1]->query->includes;

        $this->assertEquals('foo', $fields[0]);
        $this->assertEquals('barr', $fields[1]);
        $this->assertEquals('baz', $fields[2]);
        
        $filters->each(function ($filter) {
            $this->assertTrue($filter instanceof Filter);
        });

        $this->assertEquals(1, $filters->count());
        $this->assertEquals('foo', $filters[0]->name);
        $this->assertEquals('=', $filters[0]->operator);
        $this->assertEquals('bar', $filters[0]->value);

        $sorts->each(function ($sort) {
            $this->assertTrue($sort instanceof Sort);
        });

        $this->assertEquals(1, $sorts->count());
        $this->assertEquals('foo', $sorts[0]->name);
        $this->assertEquals('asc', $sorts[0]->order);

        $this->assertEquals(0, $includes->count());
    }
}

class FooQuery extends QueryDefinition
{
    protected $fields = [
        'foo',
        'bar',
        'baz',
    ];

    protected $includes = [
        'one' => BarQuery::class,
        'two' => BarQuery::class,
        'three' => BarQuery::class,
    ];

    protected $transform = [
        'foo' => 'fooo',
        'one' => 'onee',
    ];

    public function authorizeBarField()
    {
        return true;
    }

    public function authorizeBazField()
    {
        return false;
    }

    public function authorizeTwoInclude()
    {
        return true;
    }

    public function authorizeThreeInclude()
    {
        return false;
    }
}

class BarQuery extends QueryDefinition
{
    protected $fields = [
        'foo',
        'bar',
        'baz',
    ];

    protected $includes = [
        'foo' => FooQuery::class,
        'bar' => FooQuery::class,
    ];

    protected $transform = [
        'bar' => 'barr',
    ];

    public function authorizeBarInclude()
    {
        return false;
    }
}
