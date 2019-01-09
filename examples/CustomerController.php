<?php

use IndexRequest;
use CustomerQuery;

class CustomerController
{
    use ScopesBuilderFromQuery;

    /**
     * Query the index of customer table data. Returns a synchronous collection of
     * paginated data.
     *
     * @param CustomerQuery $query
     * @param Customer $customers
     *
     * @return CustomersCollection
     */
    public function index(CustomerQuery $query)
    {
        $this->authorize('customers.query');

        $customers = Customer::fields($query->fields)
                        ->filters($query->filters)
                        ->sorts($query->sorts)
                        ->includes($query->includes)
                        ->paginate($query->limit, $query->page);

        return CustomerResource::collection($customers);
    }

    /**
     * Show a specified resource.
     *
     * @param CustomerRequest $request
     * @param integer $id
     * @return CustomersResource
     */
    public function show(CustomerQuery $query, Customer $customer)
    {
        $this->authorize('customers.show', $customer);
        $this->authorize('customers.query');

        $customer = Customer::fields($query->fields)
                        ->includes($query->includes)
                        ->findOrFail($id);

        return new CustomerResource($customer);
    }

    /**
     * Store a specified resource.
     *
     * @param CustomerRequest $request
     * @return CustomerResource
     */
    public function store(CustomerStoreRequest $request)
    {
        $this->authorize('customers.store');

        $customer = Accounts::register($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Update a specified resource.
     *
     * @param CustomerRequest $request
     * @param Customer $customer
     * @return CustomerResource
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $this->authorize('customers.update', $customer);
        
        $customer->update($request->validated());

        return new CustomerResource($customer->fresh());
    }

    /**
     * Destroy a specified resource.
     *
     * @param CustomerRequest $request
     * @param CustomerRepository $customers
     * @param Customer $customer
     * @return Response
     */
    public function destroy(Request $request, Customer $customer)
    {
        $this->authorize('customers.delete', $customer);

        $customers->deactivate($customer->id);

        return response()->successNoContent();
    }

    /**
     * Process an export request for customer data.
     *
     * @param CustomerExportRequest $request
     * @return JobAcceptedResponse
     */
    public function export(CustomerExportRequest $request)
    {
        $this->authorize('customers.export');

        CustomerExport::dispatch($request->getQuery(), $request->getChannelToken());

        return response()->jobAccepted();
    }
}
