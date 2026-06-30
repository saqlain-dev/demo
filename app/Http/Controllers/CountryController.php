<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Finance\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = Country::query()->with(['created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = Country::query()->create($this->input);
            DB::commit();
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
            'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Restricting to CSV files only
        ]);

        try {
            DB::beginTransaction();

            // Retrieve and parse the uploaded file
            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $headers = array_map('strtolower', array_shift($data)); // Extract headers (first row)

            foreach ($data as $row) {
                // Map headers to values in the row
                $rowData = array_combine($headers, $row);

                // Validate each row's data
                $validator = Validator::make($rowData, [
                    'name' => 'required|string',
                    'code' => 'required|string',
                    'status' => 'nullable|integer',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return resp(0, 'CSV contains invalid data!', $validator->errors(), Response::HTTP_BAD_REQUEST);
                }

                // Insert record
                Country::create([
                    'name' => $rowData['name'],
                    'code' => $rowData['code'],
                    'status' => $rowData['status'] ?? 1,
                    'created_by' => $rowData['created_by'] ?? null,
                    'updated_by' => $rowData['updated_by'] ?? null,
                ]);
            }

            DB::commit();
            return resp(1, 'CSV imported successfully!', null, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to import CSV!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Country $country): JsonResponse
    {
        $taxRate = $country->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $taxRate, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $country->update($this->input);
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
    public function destroy(Country $country): JsonResponse
    {
        $item = $country->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
