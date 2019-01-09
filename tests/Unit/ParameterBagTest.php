<?php

namespace ResourceQuery\Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use ResourceQuery\Query\Sort;
use ResourceQuery\Query\Filter;
use ResourceQuery\Query\Relation;
use ResourceQuery\Query\ParameterBag;
use ResourceQuery\Query\QueryDefinition;
use ResourceQuery\Request\JsonAdapter;

class ParameterBagTest extends TestCase
{
    public function testParameterBagInit()
    {
        $fields = [
            'first_name' => true,
            'last_name' => true,
            'email' => true,
            'age' => true,
        ];
        $filters = [
            'role' => 'customer,public',
            'age' => [
                'min' => 10,
                'max' => 25,
            ]
        ];
        $sorts = [
            'age' => 'asc',
            'created_at' => 'desc',
        ];
        $includes = [
            'comments' => new CommentQuery(new Request(), new JsonAdapter()),
            'posts' => new PostQuery(new Request(), new JsonAdapter()),
        ];
        $page = 10;
        $limit = 100;

        $parameters = new ParameterBag($fields, $filters, $sorts, $includes, $page, $limit);
        
        $this->assertTrue($parameters instanceof ParameterBag);

        $this->assertCount(4, $parameters->fields);
        $this->assertEquals($parameters->fields[0], 'first_name');
        $this->assertEquals($parameters->fields[1], 'last_name');
        $this->assertEquals($parameters->fields[2], 'email');
        $this->assertEquals($parameters->fields[3], 'age');

        $this->assertCount(3, $parameters->filters);
        $this->assertTrue($parameters->filters[0] instanceof Filter);
        $this->assertTrue($parameters->filters[1] instanceof Filter);
        $this->assertTrue($parameters->filters[2] instanceof Filter);

        $this->assertCount(2, $parameters->sorts);
        $this->assertTrue($parameters->sorts[0] instanceof Sort);
        $this->assertTrue($parameters->sorts[1] instanceof Sort);

        $this->assertCount(2, $parameters->includes);
        $this->assertTrue($parameters->includes[0] instanceof Relation);
        $this->assertTrue($parameters->includes[1] instanceof Relation);
        
        $this->assertEquals(10, $parameters->page);
        $this->assertEquals(100, $parameters->limit);
    }
}

class CommentQuery extends QueryDefinition
{
}

class PostQuery extends QueryDefinition
{
}
