<?php

namespace ResourceQuery\Tests\Unit\Request;

use Tests\TestCase;
use ResourceQuery\Request\JsonAdapter;

class JsonAdapterTest extends TestCase
{
    public function mockQueryParams()
    {
        return [
            'fields' => 'foo,bar',
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
                'foo' => [],
                'bar' => [
                    'fields' => 'foo,bar,baz',
                    'filter' => [
                        'foo' => 'bar',
                    ]
                ]
            ]
        ];
    }

    public function testAdapterGetsFields()
    {
        $requestQueryParams = $this->mockQueryParams();
        $adapter = new JsonAdapter();

        $queryParameters = $adapter->map($requestQueryParams);

        $this->assertEquals(['foo' => true, 'bar' => true], $queryParameters['fields']);
        $this->assertEquals($requestQueryParams['filters'], $queryParameters['filters']);
        $this->assertEquals($requestQueryParams['sorts'], $queryParameters['sorts']);
        $this->assertEquals([
            'foo' => [],
            'bar' => [
                'fields' => 'foo,bar,baz',
                'filter' => ['foo' => 'bar'],
            ],
        ], $queryParameters['includes']);
    }
}
