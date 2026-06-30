<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CustomerContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['customer']=CustomerContact::query()->with('customerContactStatus','salutation','gender')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'first_name' => 'required',
            'customer_contact_status' => 'required',
            'designation' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $customerContact=CustomerContact::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $customerContact->load('customerContactStatus','salutation','gender'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerContact $customer_contact)
    {
        return resp(1, 'Successful!', $customer_contact->load('customerContactStatus','salutation','gender'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerContact $customer_contact)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'first_name' => 'required',
            'customer_contact_status' => 'required',
            'designation' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $customer_contact->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $customer_contact->load('customerContactStatus','salutation','gender'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerContact $customer_contact)
    {
        $customer_contact->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
}
