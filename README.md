# LaravelResourceQuery
An alternative to fractal &amp; laravel resources for easy model querying, scoping, and filtering via API

# Introduction

This package enables a simple and flexible implementation for accessing RESTful API query parameters from a request. Each queryable endpoint has a corresponding "QueryDefinition" which defines how to filter, map and transform query parameters into a much more flexible interface for building database queries.

``` js
http.get('/users', {
  query: {
    fields: 'id,name,email',
    filters: {
      is_active: true,
      created_at: {
        min: '2018-01-01 00:00:00',
        max: '2018-12-31 23:59:59',
      }
    },
    sorts: {
      created_at: 'asc',
    },
    includes: {
      groups: {
        sorts: {
          name: 'desc',
        }
      }
    }
  }
})
```

## Defining Query Definitions

To get started, let's create a Query Definition. Definitions typically live in a directory near the Controllers that use them, but you are free to place them wherever you like. All resources extend the abstract QueryDefinition class.

``` php
<?php

namespace App;

use ResourceQuery\Query\QueryDefinition;

class UserQuery extends QueryDefinition
{
  // 
}
```

### Fields

The first step is to define which fields can be queried on the request. This will allow those fields to be filtered and sorted.

``` php
<?php

namespace App;

use ResourceQuery\Query\QueryDefinition;

class UserQuery extends QueryDefinition
{
  protected $fields = [
    'id',
    'name',
    'email',
    'created_at',
    'updated_at',
  ];
}
```

### Includes

Next we want to include which relations can be eager loaded and queried against on the request. Let's add an array of these includes to the definition:

``` php
<?php

namespace App;

use ResourceQuery\Query\QueryDefinition;

class UserQuery extends QueryDefinition
{
  protected $fields = [
    'id',
    'name',
    'email',
    'created_at',
    'updated_at',
  ];

  protected $includes = [
    'groups' => GroupQuery::class,
  ];
}
```

You will notice that we included a QueryDefinition for our `groups` include. Each include on a request should also define how that resource can be queried.

### Transforms

Sometimes the attributes coming in on a query don't correspond with the attributes in the database. To simplify building the database query, we can define how we want these field transformed in the definition.

``` php
<?php

namespace App;

use ResourceQuery\Query\QueryDefinition;

class UserQuery extends QueryDefinition
{
  protected $fields = [
    'id',
    'name',
    'email',
    'created_at',
    'updated_at',
  ];

  protected $includes = [
    'groups' => GroupQuery::class,
  ];

  protected $transform = [
    'created_at' => 'created_at_utc',
    'updated_at' => 'updated_at_utc',
  ];
}
```

### Authorization

Often it's important to limit what can be queried against depending on the user's role or permissions. Let's define some methods for determing if certain fields or includes can be accessed by the user.

Note: if there is no method defined, it is assumed to be accessible by all users.

``` php
<?php

namespace App;

use Illuminate\Http\Request;
use ResourceQuery\Query\QueryDefinition;

class UserQuery extends QueryDefinition
{
  /**
   * Authorize the `email` field to be queried.
   */
  protected function authorizeEmailField(Request $request)
  {
    return $this->allow([
      $request->user()->isAdmin(),
      $request->user()->isManager(),
    ]);
  }

  /**
   * Authorize the `groups` include to be loaded and queried.
   */
  protected function authorizeGroupInclude(Request $request)
  {
    return $this->deny([
      $request->user()->isCustomer(),
      $request->user()->isVendor(),
    ]);
  }
}
```

### Controllers

Once you have your definition for the query, let's load it into a controller and build a query.

``` php
<?php

class Controller
{
  /**
   * Query the users resource.
   *
   * @param UserQuery $query
   */
  public function index(UserQuery $query)
  {
      $users = User::fields($query->fields)
                   ->filters($query->filters)
                   ->sorts($query->sorts)
                   ->includes($query->includes)
                   ->paginate($query->limit, $query->page);

      return $users;
  }
}
```

The package comes with a handy trait for your models to make scoping simple. Here is an example of what that trait looks like:

``` php
<?php

class User extends Model
{    

    /**
     * Scopes the selected fields on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $fields
     *
     * @return void
     */
    public function scopeFields(Builder $builder, Collection $fields)
    {
        $fields->each(function ($field) use ($builder) {
            $builder->addSelect($field);
        });
    }

    /**
     * Scopes the filters on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $filters
     *
     * @return void
     */
    public function scopeFilters(Builder $builder, Collection $filters)
    {
        $filters->each(function (Filter $filter) use ($builder) {
            $builder->where($filter->name, $filter->operator, $filter->value);
        });
    }

    /**
     * Scopes the sorting of fields on a database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeSorts(Builder $builder, Collection $sorts)
    {
        $sorts->each(function (Sort $sort) use ($builder) {
            $builder->orderBy($sort->name, $sort->order);
        });
    }

    /**
     * Scopes the eager loading and querying of relationships on the database query builder.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param \Illuminate\Support\Collection $sorts
     *
     * @return void
     */
    public function scopeIncludes(Builder $builder, Collection $includes)
    {
        $includes->each(function (Relation $relation) use ($builder) {
            $builder->with([$relation->name => function ($builder) use ($relation) {
                $this->scopeFields($builder, $relation->query->fields);
                $this->scopeFilters($builder, $relation->query->filters);
                $this->scopeSorts($builder, $relation->query->sorts);
            }]);
        });
    }
}
```
