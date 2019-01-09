<?php

namespace ResourceQuery\Contracts;

interface Adapter
{
    /**
     * Transform the query parameters from a request to a
     * normalized output.
     *
     * @param array $query
     * @return array
     */
    public function map(array $query = []);
}
