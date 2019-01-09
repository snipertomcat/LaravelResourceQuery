<?php

namespace ResourceQuery\Tests\Unit\Query;

use Carbon\Carbon;
use Tests\TestCase;
use ResourceQuery\Query\Filter;
use ResourceQuery\Query\Transform;
use Illuminate\Support\Collection;

class TransformFiltersTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config(['timestamp_format' => 'U']);
        $this->transform = new Transform();
    }

    /**
     *
     */
    public function testNoFilters()
    {
        $filters = $this->transform->filters(null);
        $this->assertTrue($filters instanceof Collection);
        $this->assertTrue($filters->isEmpty());
    }

    /**
     *
     */
    public function testTwoFilters()
    {
        $query = [
            'name' => 'test',
            'email' => 'test@test.com',
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'name');
        $this->assertEquals($filters[0]->operator, '=');
        $this->assertEquals($filters[0]->value, 'test');
        $this->assertTrue($filters[1] instanceof Filter);
        $this->assertEquals($filters[1]->name, 'email');
        $this->assertEquals($filters[1]->operator, '=');
        $this->assertEquals($filters[1]->value, 'test@test.com');
    }

    /**
     *
     */
    public function testFilterNotNull()
    {
        $query = [
            'name' => null,
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 1);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'name');
        $this->assertEquals($filters[0]->operator, '!=');
        $this->assertEquals($filters[0]->value, null);
    }

    /**
     *
     */
    public function testFilterIsNull()
    {
        $query = [
            'name' => 'null',
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 1);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'name');
        $this->assertEquals($filters[0]->operator, '=');
        $this->assertEquals($filters[0]->value, null);
    }

    /**
     *
     */
    public function testTimestampFilters()
    {
        $query = [
            'created_at' => Carbon::now()->timestamp,
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 1);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'created_at');
        $this->assertEquals($filters[0]->operator, '=');
        $this->assertTrue(is_int($filters[0]->value));
    }

    /**
     *
     */
    public function testMinFilter()
    {
        $query = [
            'created_at' => [
                'min' => Carbon::now()->timestamp,
            ],
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 1);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'created_at');
        $this->assertEquals($filters[0]->operator, '>=');
        $this->assertTrue(is_int($filters[0]->value));
    }

    /**
     *
     */
    public function testMinMaxFilter()
    {
        $query = [
            'created_at' => [
                'min' => Carbon::now()->timestamp,
                'max' => Carbon::now()->timestamp,
            ],
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 2);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'created_at');
        $this->assertEquals($filters[0]->operator, '>=');
        $this->assertTrue(is_int($filters[0]->value));
        $this->assertTrue($filters[1] instanceof Filter);
        $this->assertEquals($filters[1]->name, 'created_at');
        $this->assertEquals($filters[1]->operator, '<=');
        $this->assertTrue(is_int($filters[1]->value));
    }

    /**
     *
     */
    public function testMultipleNestedFilters()
    {
        $query = [
            'name' => 'foo',
            'created_at' => [
                'min' => Carbon::now()->timestamp,
                'max' => Carbon::now()->timestamp,
            ],
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 3);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'name');
        $this->assertEquals($filters[0]->operator, '=');
        $this->assertEquals($filters[0]->value, 'foo');
        $this->assertTrue($filters[1] instanceof Filter);
        $this->assertEquals($filters[1]->name, 'created_at');
        $this->assertEquals($filters[1]->operator, '>=');
        $this->assertTrue(is_int($filters[1]->value));
        $this->assertTrue($filters[2] instanceof Filter);
        $this->assertEquals($filters[2]->name, 'created_at');
        $this->assertEquals($filters[2]->operator, '<=');
        $this->assertTrue(is_int($filters[2]->value));
    }

    /**
     *
     */
    public function testContainsFilter()
    {
        $query = [
            'status' => 'active,disabled',
        ];
        $filters = $this->transform->filters($query);
        $this->assertTrue($filters instanceof Collection);
        $this->assertEquals($filters->count(), 1);
        $this->assertTrue($filters[0] instanceof Filter);
        $this->assertEquals($filters[0]->name, 'status');
        $this->assertEquals($filters[0]->operator, '=');
        $this->assertEquals($filters[0]->value, ['active', 'disabled']);
    }
}
