<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\TaxManagement;
use App\Models\Finance\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class TaxManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = TaxManagement::query()->with(['TaxType','TaxComputation','CountryId','CoaId','ProvinceId','taxScope','taxGroup','created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_name' => 'required',
            'tax_type' => 'required',
            'tax_computation' => 'required',
            'tax_scope' => 'required',
            //'amount' => 'required',
            'filer_percentage' => 'required',
            'non_filer_percentage' => 'required',
            'late_filer_percentage' => 'required',
            'invoice_label' => 'required',
            'tax_group' => 'required',
            'description' => 'required',
            'country_id' => 'nullable|exists:countries,id',
            'province_id' => 'required|exists:provinces,id',
            'coa_id' => 'required|exists:chart_of_accounts,id',
        ]);
        try {
            DB::beginTransaction();
            $item = TaxManagement::query()->create($this->input);
            DB::commit();
            $item->load(['TaxType','TaxComputation','CountryId','CoaId','ProvinceId','taxScope','taxGroup','created_by']);
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function importCsv(Request $request)
    {
        // Validate file input
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Load and parse CSV file
            $file = $request->file('csv_file');
            $fileContent = file($file->getRealPath());
            $data = array_map('str_getcsv', $fileContent);

            // Extract headers and normalize to lowercase for mapping
            $headers = array_map('strtolower', array_shift($data));

            // Ensure headers match the expected structure
            $expectedHeaders = [
                'tax_name', 'tax_type', 'tax_computation', 'tax_scope', 'amount',
                'filer_percentage', 'non_filer_percentage', 'late_filer_percentage',
                'invoice_label', 'tax_group', 'description', 'country_id'
            ];
            $missingHeaders = array_diff($expectedHeaders, $headers);

            if (!empty($missingHeaders)) {

                return resp(0, 'CSV headers are invalid or missing!', [
                    'missing_headers' => $missingHeaders
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($data as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map headers to row values
                $rowData = array_combine($headers, $row);

                // Validate each row
                $validator = Validator::make($rowData, [
                    'tax_name' => 'required|string|max:255',
                    'tax_type' => 'required|integer',
                    'tax_computation' => 'required|integer',
                    'tax_scope' => 'required|integer',
                    'amount' => 'required|numeric',
                    'filer_percentage' => 'required|string',
                    'non_filer_percentage' => 'required|string',
                    'late_filer_percentage' => 'required|string',
                    'invoice_label' => 'required|string|max:255',
                    'tax_group' => 'required|integer',
                    'description' => 'required|string',
                    'country_id' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return resp(0, 'CSV contains invalid data!', [
                        'errors' => $validator->errors(),
                        'row_data' => $rowData,
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Insert row into database
                TaxManagement::create($rowData);
            }

            DB::commit();
            return resp(1, 'CSV imported successfully!', null, Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to import CSV!!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxManagement $taxManagement): JsonResponse
    {
        $taxManagement = $taxManagement->load(['TaxType','TaxComputation','CountryId','taxScope','taxGroup','created_by','updated_by']);
        return resp('1', 'Successful!', $taxManagement, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxManagement $taxManagement)
    {
        $request->validate([
            'tax_name' => 'required',
            'tax_type' => 'required',
            'tax_computation' => 'required',
            'tax_scope' => 'required',
            //'amount' => 'required',
            'filer_percentage' => 'required',
            'non_filer_percentage' => 'required',
            'late_filer_percentage' => 'required',
            'invoice_label' => 'required',
            'tax_group' => 'required',
            'description' => 'required',
            'country_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $taxManagement->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxManagement $taxManagement): JsonResponse
    {
        $item = $taxManagement->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
