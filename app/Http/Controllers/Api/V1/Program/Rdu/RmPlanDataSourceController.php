<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Enum\RmMethodology;
use App\Http\Controllers\Controller;
use App\Models\Program\Rdu\RmPlanDataSource;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RmPlanDataSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RmPlanDataSource::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rm_plan_id' => 'required|integer|exists:rm_plans,id',
            'rm_data_source' => 'required|integer',
            'rm_data_availability' => 'required|integer',
        ]);
        $item = RmPlanDataSource::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(RmPlanDataSource $rmPlanDataSource): JsonResponse
    {
        return resp('1', 'Successful!', $rmPlanDataSource, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RmPlanDataSource $rmPlanDataSource)
    {
        $request->validate([
            'rm_plan_id' => 'required|integer|exists:rm_plans,id',
            'rm_data_source' => 'required|integer',
            'rm_data_availability' => 'required|integer',
        ]);
        $item = $rmPlanDataSource->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RmPlanDataSource $rmPlanDataSource)
    {
        $rmPlanDataSource->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
    public function getRMDropDown(){
        $data['rm_data_source']= Type::getTypeValues('rm-data-source');
        $data['rm_data_availability']= Type::getTypeValues('rm-data-availability');
        $data['rm_methodology']= RmMethodology::cases();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}
