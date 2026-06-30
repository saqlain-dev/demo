<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use App\Http\Controllers\Controller;
use App\Models\Finance\Audit\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            '*.reference_number' => 'required|string',
            '*.filing_status' => 'required|integer',
            '*.tax_type' => 'required|integer',
            '*.voucher_id' => 'required_if:*.tax_type,2|integer',
            '*.employee_id' => 'required_if:*.tax_type,1|integer',
        ]);


        try {
            DB::beginTransaction();
            $this->input = array_map(function ($data) {
                return array_merge($data, ['created_by' => auth()->id()]); // Assuming you're using Laravel authentication
            }, $this->input);
            $item = TaxFilingStatus::query()->insert($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditTrail $auditTrail)
    {
        //
    }
}
