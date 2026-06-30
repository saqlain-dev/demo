<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\RfqType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RfqTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $data = RfqType::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'item_category_create'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $rfqType = RfqType::query()->create($this->input);
        return resp(1, 'Successful!', $rfqType, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(RfqType $rfqType)
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        return resp(1, 'Successful!', $rfqType, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RfqType $rfqType)
    {
        $this->authorizeAny([
            'item_category_update'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);
        $rfqType->update($this->input);
        return resp(1, 'Successful!', $rfqType, Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RfqType $rfqType)
    {
        $this->authorizeAny([
            'item_category_delete'
        ]);

        $rfqType->delete();
        $message = "Item Deleted Successfully";
        return resp(1, 'Successful!', $message, Response::HTTP_CREATED);
    }
}
