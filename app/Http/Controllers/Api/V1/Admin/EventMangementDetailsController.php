<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventManagementDetail;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventMangementDetailsController extends Controller
{
    //index 
    public function index()
    {
       $eventManagementDetails = EventManagementDetail::with(['eventManagement', 'roomType','seatingArrangement','boardType','procurementDetails', 'createdBy', 'updatedBy'])->get();
       return resp('1', 'Record Fetched Successfully!', $eventManagementDetails, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        $request->validate([
            'event_management_id' => 'required|exists:event_management,id',
            'room_type_id' => 'nullable',
            'seating_arrangement_id' => 'nullable',
            'board_type_id' => 'nullable',
            'procurement_details_id' => 'nullable',
        ]);

        if($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->start_date)->format('Y-m-d H:i:s');
            $endDate   = Carbon::createFromFormat('Y-m-d\TH:i', $request->end_date)->format('Y-m-d H:i:s');

            $request->merge([
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);
        }

        $eventManagementDetail = EventManagementDetail::create($request->all());
        if ($request->hasFile('attachment')) {
            $path = $this->saveFile($request, 'eventManagement');
            $eventManagementDetail->update(['attachment' => $path]);
        }
        $eventManagementDetail->load(['eventManagement', 'roomType','seatingArrangement','boardType','procurementDetails']);
        return resp('1', 'Record Created Successfully!', $eventManagementDetail, Response::HTTP_CREATED);
    }

    //show
    public function show(EventManagementDetail $eventManagementDetail)
    {
        $eventManagementDetail->load(['eventManagement', 'roomType','seatingArrangement','boardType','procurementDetails', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Fetched Successfully!', $eventManagementDetail, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, EventManagementDetail $eventManagementDetail)
    {
        $request->validate([
            'room_type_id' => 'nullable',
            'seating_arrangement_id' => 'nullable',
            'board_type_id' => 'nullable',
            'procurement_details_id' => 'nullable',
        ]);
         if($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->start_date)->format('Y-m-d H:i:s');
            $endDate   = Carbon::createFromFormat('Y-m-d\TH:i', $request->end_date)->format('Y-m-d H:i:s');

            $request->merge([
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);
        }
        $eventManagementDetail->update($request->all());
        if ($request->hasFile('attachment')) {
            $path = $this->saveFile($request, 'eventManagement');
            $eventManagementDetail->update(['attachment' => $path]);
        }
        $eventManagementDetail->load(['eventManagement', 'roomType','seatingArrangement','boardType','procurementDetails', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Updated Successfully!', $eventManagementDetail, Response::HTTP_OK);
    }

    //destroy
    public function destroy(EventManagementDetail $eventManagementDetail)
    {
        $eventManagementDetail->delete();
        return resp('1', 'Record Deleted Successfully!', null, Response::HTTP_NO_CONTENT);
    }

    public function saveFile(Request $request, $folder)
    {
        $file = $request->file('attachment');

        // Save inside "public/uploads/media/{folder}"
        $path = public_path('uploads/media/' . $folder);

        // Make directories if missing
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate safe filename
        $extension = $file->getClientOriginalExtension();
        $filename  = time() . '_' . uniqid() . '.' . $extension;

        // Move file
        $file->move($path, $filename);

        // Return relative path for DB storage
        return 'uploads/media/' . $folder . '/' . $filename;
    }

}
