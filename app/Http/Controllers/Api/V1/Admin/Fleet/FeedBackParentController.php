<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\FeedBack;
use Illuminate\Http\Response;

class FeedBackParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = FeedBack::with(['fleetFeedBacks.requisition', 'fleetFeedBacks.question'])->get();

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
                'employee_id' => 'required',
                'designation_id' => 'required',
                'date' => 'required',
                'type' => 'required',
             ]);
            $item = FeedBack::query()->create($this->input);

            DB::commit();
            return resp('1', 'Added Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to add feed back. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $requisition = FeedBack::with('fleetFeedBacks.requisition','fleetFeedBacks.question')->find($id);
        return resp('1', 'Successful!', $requisition, Response::HTTP_OK);
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
                'employee_id' => 'required',
                'designation_id' => 'required',
                'date' => 'required',
                'type' => 'required',
             ]);
            $item = FeedBack::query()->findOrFail($id);
            $item->update($this->input);

            DB::commit();
            return resp('1', 'Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update feed back. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $feedback = FeedBack::query()->findOrFail($id);
        $item = $feedback->delete();
        return resp('1', 'deleted Successfully!', $item, Response::HTTP_OK);
    }
}
