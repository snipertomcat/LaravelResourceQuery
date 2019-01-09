<?php

namespace ResourceQuery;

use ResourceQuery\Contracts\Adapter;
use ResourceQuery\Query\QueryDefinition;
use ResourceQuery\Request\JsonAdapter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class ResourceQueryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Adapter::class, JsonAdapter::class);

        $this->app->resolving(QueryDefinition::class, function ($resource, $app) {
            $this->setQueryFromRequest($resource, $app['request']);
        });
    }

    /**
     * Set the query parameters on the resource from the request.
     *
     * @param  \ResourceQuery\Query\QueryDefinition  $resource
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return void
     */
    protected function setQueryFromRequest(QueryDefinition $resource, Request $request)
    {
        $resource->setParameters($request->query->all(), true);
    }
}
