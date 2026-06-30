<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Chauffeur;
use App\Models\Admin\Fleet\IncidentReport;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class IncidentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'accident_prevention_view'
        ]);

        $data = IncidentReport::with(['VehicleId','ReportType','ChauffeurId','created_by','updated_by','CorrectiveAction.PersonResponsible'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'accident_prevention_create'
        ]);

        $request->validate([
            'vehicle_id' => 'required',
            'report_type' => 'required',
            'is_notifiable' => 'required',
            'reporting_station' => 'required',
            'incident_location' => 'required',
            'incident_date' => 'required',
            'incident_time' => 'required',
            'reporting_date' => 'required',
            'chauffeur_id' => 'required',
            //'accident_report_no' => 'required',
            'incident_reporting_person' => 'required',
            'nature_of_damage' => 'required',
            'nature_of_injuries' => 'required',
            'accident_description' => 'required',
            'human_injuries_description' => 'required',
            'third_party_involvement' => 'required',
            'investigation_findings' => 'required',
            'recommendation' => 'required',
            'is_payment_made' => 'required',
            'is_payment_received' => 'required',
        ]);
        $maxValue = IncidentReport::max('accident_report_no');
        if ($maxValue) {
            // Extract the number part
            $lastNumber = (int) substr($maxValue, -3);
            // Increment the number
            $nextNumber = $lastNumber + 1;
            // Format the new accident report number
            $newReportNo = 'INC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing reports, start from INC-001
            $newReportNo = 'INC-001';
        }
        $this->input['accident_report_no'] = $newReportNo;
        if ($request->hasFile('incident_images')) {
            $responses = $this->saveIncidentImages($request, 'incident_images');
            $this->input['incident_images'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = IncidentReport::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveIncidentImages($request, $folder)
    {
        $images = $request->file('incident_images');
        $paths = [];
        // Ensure $images is an array
        if (!is_array($images)) {
            $images = [$images];
        }
        foreach ($images as $image) {
            $path = 'uploads/media/' . $folder;

            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (!file_exists('uploads/media')) {
                mkdir('uploads/media', 0777, true);
            }
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename = time() . '_' . $image->getClientOriginalName();
            $file_name = str_replace(' ', '_', $filename);
            $image->move($path, $file_name);

            // Save the path to the array
            $paths[] = $path . '/' . $file_name;
        }

        return json_encode($paths);
    }


    /**
     * Display the specified resource.
     */
    public function show(IncidentReport $incidentReport): JsonResponse
    {
        $this->authorizeAny([
            'accident_prevention_view'
        ]);

        $incidentReport = $incidentReport->load(['VehicleId','ReportType','ChauffeurId','created_by','updated_by','CorrectiveAction.PersonResponsible']);
        return resp('1', 'Successful!', $incidentReport, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncidentReport $incidentReport)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'report_type' => 'required',
            'is_notifiable' => 'required',
            'reporting_station' => 'required',
            'incident_location' => 'required',
            'incident_date' => 'required',
            'incident_time' => 'required',
            'reporting_date' => 'required',
            'chauffeur_id' => 'required',
            //'accident_report_no' => 'required',
            'incident_reporting_person' => 'required',
            'nature_of_damage' => 'required',
            'nature_of_injuries' => 'required',
            'accident_description' => 'required',
            'human_injuries_description' => 'required',
            'third_party_involvement' => 'required',
            'investigation_findings' => 'required',
            'recommendation' => 'required',
            'is_payment_made' => 'required',
            'is_payment_received' => 'required',
        ]);
        if ($request->hasFile('incident_images')) {
            $responses = $this->saveIncidentImages($request, 'incident_images');
            $this->input['incident_images'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = $incidentReport->update($this->input);
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
    public function destroy(IncidentReport $incidentReport): JsonResponse
    {
        $this->authorizeAny([
            'accident_prevention_delete'
        ]);

        $item = $incidentReport->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getFleetDropDown(){
        $data['projects']= ProjectProfile::query()->where(['approval_status'=>1])->get();
        $data['vehicle_type']= Type::getTypeValues('vehicle-type');
        $data['report_type']= Type::getTypeValues('report-type');
        $data['fuel_card']= Type::getTypeValues('fuel-card');
        //$data['chauffeurs']= Employee::query()->where(['designation_id'=>13, 'employee_type'=>13])->get();
        //$data['chauffeurs']= Employee::query()->where(['designation_id'=>28, 'employee_type'=>13])->get();
        $data['chauffeurs'] = Employee::query()
            ->whereIn('designation_id', [116, 28])
            ->where('employee_type', 13)
            ->get();
        $data['employees']= Employee::query()->where(['employee_type'=>13])->get();
        $data['vehicles']= Vehicle::all();
        $data['visit_type']= Type::getTypeValues('visit-type');
        $data['feedback_type']= Type::getTypeValues('feedback-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
