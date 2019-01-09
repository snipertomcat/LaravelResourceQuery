<?php

namespace ResourceQuery\Tests\Unit\Query;

use Carbon\Carbon;
use Tests\TestCase;
use ResourceQuery\Query\Sort;
use ResourceQuery\Query\Filter;
use ResourceQuery\Query\Relation;
use Illuminate\Support\Collection;
use ResourceQuery\Query\Transform;
use ResourceQuery\Query\ParameterBag;
use ResourceQuery\Query\QueryDefinition;

class TransformIncludesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->transform = new Transform();
    }

    /**
     *
     */
    public function testNoIncludes()
    {
        $includes = $this->transform->includes(null);
        $this->assertTrue($includes instanceof Collection);
        $this->assertTrue($includes->isEmpty());
    }

    /**
     *
     */
    public function testSimpleIncludes()
    {
        $query = [
            'comments' => app(CommentsQuery::class),
            'users.posts' => app(PostsQuery::class),
        ];
        
        $includes = $this->transform->includes($query);
        
        $this->assertTrue($includes instanceof Collection);
        $this->assertEquals($includes->count(), 2);

        $this->assertTrue($includes[0] instanceof Relation);
        $this->assertEquals($includes[0]->name, 'comments');
        $this->assertTrue($includes[0]->query instanceof CommentsQuery);
        $this->assertTrue($includes[0]->query->fields instanceof Collection);
        $this->assertTrue($includes[0]->query->filters instanceof Collection);
        $this->assertTrue($includes[0]->query->sorts instanceof Collection);

        $this->assertTrue($includes[1] instanceof Relation);
        $this->assertEquals($includes[1]->name, 'users.posts');
        $this->assertTrue($includes[1]->query instanceof PostsQuery);
        $this->assertTrue($includes[1]->query->fields instanceof Collection);
        $this->assertTrue($includes[1]->query->filters instanceof Collection);
        $this->assertTrue($includes[1]->query->sorts instanceof Collection);
    }
}

class CommentsQuery extends QueryDefinition
{
}

class PostsQuery extends QueryDefinition
{
}
