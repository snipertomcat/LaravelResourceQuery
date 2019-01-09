<?php

namespace ResourceQuery\Tests\Unit\Query;

use Carbon\Carbon;
use ResourceQuery\Query\Transform;
use ResourceQuery\Query\Sort;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TransformSortsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->transform = new Transform();
    }

    /**
     *
     */
    public function testNoSortOrder()
    {
        $sorting = $this->transform->sorts(null);
        $this->assertTrue($sorting instanceof Collection);
        $this->assertTrue($sorting->isEmpty());
    }

    /**
     *
     */
    public function testSorting()
    {
        $query = [
            'name' => 'asc',
            'age' => 'desc',
        ];
        $sorting = $this->transform->sorts($query);
        $this->assertTrue($sorting instanceof Collection);
        $this->assertEquals($sorting->count(), 2);
        $this->assertTrue($sorting[0] instanceof Sort);
        $this->assertEquals($sorting[0]->name, 'name');
        $this->assertEquals($sorting[0]->order, 'asc');
        $this->assertTrue($sorting[1] instanceof Sort);
        $this->assertEquals($sorting[1]->name, 'age');
        $this->assertEquals($sorting[1]->order, 'desc');
    }

    /**
     *
     */
    public function testSortingDefaultOrder()
    {
        $query = [
            'name' => null,
        ];
        $sorting = $this->transform->sorts($query);
        $this->assertTrue($sorting instanceof Collection);
        $this->assertEquals($sorting->count(), 1);
        $this->assertTrue($sorting[0] instanceof Sort);
        $this->assertEquals($sorting[0]->name, 'name');
        $this->assertEquals($sorting[0]->order, 'asc');
    }
}
