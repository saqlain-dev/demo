<?php

namespace App\Http\Controllers\API\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\VehicleRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleRecordController extends Controller
{
     //index
    public function index(Request $request)
    {
        $vehicleRecords = VehicleRecord::with('vehicle')->get();
        return resp('1', 'Successful!', $vehicleRecords, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|integer|in:1,2',
            'remarks' => 'nullable|string',
        ]);

        try {
            if($request->file('attachment')){
                $response=$this->saveAttachment($request,'vehicle_attachments');
                $data['attachment'] = $response;
            }
            $vehicleRecord = VehicleRecord::create($data);
            $vehicleRecord->load('vehicle'); 
            return resp('1', 'Record Created Successfully!', $vehicleRecord, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            //throw $th;
            return resp('0', 'Failed to create vehicle record.', null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //show
    public function show($id)
    {
        $vehicleRecord = VehicleRecord::with('vehicle')->findOrFail($id);
        if (!$vehicleRecord) {
            return resp('0', 'Vehicle record not found.', null, Response::HTTP_NOT_FOUND);
        }
        $vehicleRecord->load('vehicle'); // Ensure vehicle relationship is loaded
       return resp('1', 'Record Retrieved Successfully!', $vehicleRecord, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, $id)
    {
        $vehicleRecord = VehicleRecord::findOrFail($id);
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|integer|in:1,2',
            'remarks' => 'nullable|string',
        ]);

        if ($request->file('attachment')) {
            $response = $this->saveAttachment($request, 'vehicle_attachments');
            $data['attachment'] = $response;
        }

        $vehicleRecord->update($data);
        $vehicleRecord->load('vehicle'); 
        return resp('1', 'Record Updated Successfully!', $vehicleRecord, Response::HTTP_OK);
    }

    //destroy
    public function destroy($id)
    {
        $vehicleRecord = VehicleRecord::findOrFail($id);
        if (!$vehicleRecord) {
            return resp('0', 'Vehicle record not found.', null, Response::HTTP_NOT_FOUND);
        }

        $vehicleRecord->delete();
        return resp('1', 'Vehicle record deleted successfully.', null, Response::HTTP_OK);
    }

    public function saveAttachment($request, $folder)
    {

        $file = $request->file('attachment');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }
}
