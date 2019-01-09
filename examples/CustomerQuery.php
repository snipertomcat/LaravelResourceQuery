<?php

use Customer;
use MetricsQuery;
use PersonalQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use ResourceQuery\Query\QueryDefinition;

class CustomerQuery extends QueryDefinition
{
    /**
     * The fields that can be queried against.
     *
     * @var array
     */
    protected $fields = [
        'name',
        'email',
        'referral_source',
        'joined_at',
        'account_expired_at',
        'status',
    ];

    /**
     * The relations that can be eager loaded and queried against.
     *
     * @var array
     */
    protected $includes = [
        'personal' => PersonalQuery::class,
        'metrics' => MetricsQuery::class,
    ];

    /**
     * Transformations on the names of fields / relations after authorization
     * has been checked.
     *
     * @var array
     */
    protected $transform = [
        'account_expired_at' => 'account_expired_at_utc',
    ];

    /**
     * Determine if the attribute can be queried.
     *
     * @var \Illuminate\Http\Request $request
     *
     * @return array|bool
     */
    public function authorizeEmailField(Request $request)
    {
        return $this->allow([
            $request->user()->isAdmin(),
            $request->user()->isCustomerService(),
        ]);
    }

    /**
     * Determine if the attribute can be queried.
     *
     * @var \Illuminate\Http\Request $request
     *
     * @return array|bool
     */
    public function authorizeReferralSourceField(Request $request)
    {
        return $this->allow([
            $request->user()->isAdmin(),
            $request->user()->isAffiliate(),
            $request->user()->isAdPartner()
        ]);
    }

    /**
     * Determine if the relationship should be queryable.
     *
     * @var \Illuminate\Http\Request $request
     *
     * @return array|bool
     */
    public function authorizePersonalRelation(Request $request)
    {
        return $this->allow([
            $request->user()->isAdmin(),
            $request->user()->isCustomerService(),
        ]);
    }

    /**
     * Determine if the relationship should be queryable.
     *
     * @var \Illuminate\Http\Request $request
     *
     * @return array|bool
     */
    public function authorizeMetricsRelation(Request $request)
    {
        return $this->allow([
            $request->user()->isAdmin(),
            $request->user()->isReporter(),
        ]);
    }
}
