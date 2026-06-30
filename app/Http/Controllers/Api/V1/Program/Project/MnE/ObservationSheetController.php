<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Controller;
use App\Models\Admin\Library\Book;
use App\Models\District;
use App\Models\Employee;
use App\Models\Program\Project\MnE\ObservationSheet;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ObservationSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ObservationSheet::with(['TypeOfActivity','MneOfficerId','DistrictId','created_by','updated_by','MneObservations' => ['TypeOfRedFlag','Priority','ProgrammaticResponses']])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mne_plan_id' => 'required',
            'date' => 'required',
            'type_of_activity' => 'required',
            'mne_officer_id' => 'required',
            'district_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = ObservationSheet::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ObservationSheet $observationSheet): JsonResponse
    {
        $observationSheet = $observationSheet->load(['TypeOfActivity','MneOfficerId','DistrictId','created_by','updated_by','MneObservations' => ['TypeOfRedFlag','Priority','ProgrammaticResponses']]);
        return resp('1', 'Successful!', $observationSheet, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ObservationSheet $observationSheet)
    {
        $request->validate([
            'mne_plan_id' => 'required',
            'date' => 'required',
            'type_of_activity' => 'required',
            'mne_officer_id' => 'required',
            'district_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $observationSheet->update($this->input);
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
    public function destroy(ObservationSheet $observationSheet): JsonResponse
    {
        //$observationSheet->MneObservations()->ProgrammaticResponses()->delete();
        $observationSheet->MneObservations()->each(function ($mneObservation) {
            $mneObservation->ProgrammaticResponses()->delete();
        });
        $observationSheet->MneObservations()->delete();
        $item = $observationSheet->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function ObservationDropdown(){
        $data['type_of_red_flag']= Type::getTypeValues('type-of-red-flag');
        $data['observation_activity_type']= Type::getTypeValues('observation-activity-type');
        $data['priority']= Type::getTypeValues('priority');
        $data['employees']= Employee::all();
        $data['district']= District::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
