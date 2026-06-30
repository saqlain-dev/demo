<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LogBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'log_book_view'
        ]);
        $data = Vehicle::with(['LogBooks'=>['VehicleType','DriverId','VisitType','created_by','updated_by'],'VehicleType'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'log_book_create'
        ]);

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required_without:non_pool_driver',
            'non_pool_driver' => 'required_without:driver_id',
            'visit_type' => 'required',
            'date' => 'required',
            // 'vehicle_type' => 'required',
            'odo_meter_start' => 'required',
            'odo_meter_end' => 'required',
            'time_in' => 'required',
            'time_out' => 'required',
            // 'pool_type' => 'required',
            'officials_names' => 'nullable|string|max:255',
            //'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = LogBook::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LogBook $logBook): JsonResponse
    {
        $this->authorizeAny([
            'log_book_view'
        ]);

        $logBook = $logBook->load(['VehicleId','VehicleType','DriverId','VisitType','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LogBook $logBook)
    {
        $this->authorizeAny([
            'log_book_update'
        ]);

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'visit_type' => 'required',
         //   'vehicle_type' => 'required',
            'odo_meter_start' => 'required',
            'odo_meter_end' => 'required',
            'time_in' => 'required',
            'time_out' => 'required',
          //  'pool_type' => 'required',
            'officials_names' => 'nullable|string|max:255',
            //'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $logBook->update($this->input);
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
    public function destroy(LogBook $logBook): JsonResponse
    {
        $this->authorizeAny([
            'log_book_delete'
        ]);

        $item = $logBook->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
