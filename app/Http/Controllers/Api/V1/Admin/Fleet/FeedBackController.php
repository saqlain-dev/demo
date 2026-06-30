<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\FeedBack;
use App\Models\Admin\Fleet\FleetFeedBack;
use App\Models\Admin\Fleet\VehicleRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class FeedBackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FleetFeedBack::with('question','requisition','employee.designation')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'requisition_id' => 'required',
                'employee_id' => 'required',
                'type' => 'required',
                // 'feed_back_id' => 'required',
             ]);

             foreach ($request->answers as $answer) {
                $fleetFeedBack = new FleetFeedBack();
                $fleetFeedBack->requisition_id = $request->requisition_id;
                $fleetFeedBack->employee_id = $request->employee_id;
                $fleetFeedBack->date = $request->date;
                $fleetFeedBack->type = $request->type;
                $fleetFeedBack->question_id = $answer["question_id"];
                $fleetFeedBack->answer = $answer["answer"];
                $fleetFeedBack->save();
             }
            DB::commit();
            return resp('1', 'Added Successfully!', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to add feed back. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data['vehicle_request'] = VehicleRequest::with('feedBack.question', 'feedBack.employee.designation')->find($id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'requisition_id' => 'required',
                'employee_id' => 'required',
                'type' => 'required',
                // 'feed_back_id' => 'required',
            ]);

            $feedBack = FleetFeedBack::query()->findOrFail($id);
            FleetFeedBack::where('requisition_id', $feedBack->requisition_id)->delete();

            foreach ($request->answers as $answer) {
                $fleetFeedBack = new FleetFeedBack();
                $fleetFeedBack->requisition_id = $request->requisition_id;
                $fleetFeedBack->employee_id = $request->employee_id;
                $fleetFeedBack->date = $request->date;
                $fleetFeedBack->type = $request->type;
                $fleetFeedBack->question_id = $answer["question_id"];
                $fleetFeedBack->answer = $answer["answer"];
                $fleetFeedBack->save();
             }

            DB::commit();
            return resp('1', 'Updated Successfully!', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $feedback = FleetFeedBack::query()->findOrFail($id);
        $item = $feedback->delete();
        return resp('1', 'deleted Successfully!', $item, Response::HTTP_OK);
    }
}
