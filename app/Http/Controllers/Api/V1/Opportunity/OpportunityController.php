<?php

namespace App\Http\Controllers\Api\V1\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\Configuration\ScaleRating;
use App\Models\Customer;
use App\Models\Division\Division;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\Prospect;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class OpportunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'opportunities_view',
        ]);
        /*$data['task_listing']=Opportunity::query()->with(['opportunityable','opportunityType','salesStage','opportunityStatus','opportunityOwner','opp_activity','stage_rating.stage','priority_level','comments.createdBy','attachments.createdBy','rfp' => ['rfpDetail.item','rfpDetail.uom','rfpDetail.division', 'rfpStatus','rfq'=>['rfqStatus','supplier','rfqDetail.uom','rfqDetail.item'],'quotation'=>['quotationDetail','supplier','quotationStatus']]])->get();*/
        $data['task_listing'] = Opportunity::query()
            ->with([
                'opportunityable',
                'opportunityType',
                'salesStage',
                'opportunityStatus',
                'opportunityOwner',
                'opp_activities',
                'stage_rating.stage',
                'priority_level',
                'comments.createdBy',
                'attachments.createdBy',
                'rfp' => [
                    'rfpDetail.item',
                    'rfpDetail.uom',
                    'rfpDetail.division',
                    'rfpStatus',
                    'rfq' => [
                        'rfqStatus',
                        'supplier',
                        'rfqDetail.uom',
                        'rfqDetail.item'
                    ],
                    'quotation' => [
                        'quotationDetail',
                        'supplier',
                        'quotationStatus'
                    ]
                ]
            ])
            ->withCount('opp_activities')
            ->get();
        $total_margin_amount = 0;

// ✅ Loop through opportunities
        foreach ($data['task_listing'] as $key =>  $opportunity) {
            // ✅ Check if rfp and quotation exist
            if (!isset($opportunity->rfp) || !isset($opportunity->rfp->quotation)) {
                continue;
            }

            // ✅ Check if quotationDetail exists
            if (!isset($opportunity->rfp->quotation->quotationDetail)) {
                continue;
            }

            // ✅ Loop through quotation details and sum amounts
            foreach ($opportunity->rfp->quotation->quotationDetail as $quotationDetail) {
                $total_margin_amount += $quotationDetail->margin_rate ?? 0;

            }
            $data['task_listing'][$key]['total_margin_amount']=$total_margin_amount;
        }

// ✅ Store the sum separately


        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'opportunities_create',
        ]);
        $request->validate([
            'opportunity_name' => 'required|string|max:255',
            //'series' => 'required|string|max:255',
            'opportunity_type' => 'required|integer',
            //'sales_stage' => 'required|integer',
            'opportunityable_id' => 'required',
            'opportunityable_type' => 'required',
            'opportunity_status' => 'required',
            'opportunity_amount' => 'numeric|min:0',
            'closing_date' => 'date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('opportunities') as nextID");
            $opp_series_number='OP-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['series']=$opp_series_number;
            $this->input['closing_date']=$request->closing_date ?? NULL;
            $this->input['opportunityable_type']= $request->opportunityable_type === 'lead' ? Lead::class : ($request->opportunityable_type === 'prospect' ? Prospect::class : Customer::class);
            $opportunity=Opportunity::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $opportunity->load('opportunityable','opportunityType','salesStage','opportunityStatus','opportunityOwner','stage_rating'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Opportunity $opportunity)
    {
        $this->authorizeAny([
            'opportunities_view',
        ]);
        $scalerating=ScaleRating::query()->where('scale_stage',$opportunity->sales_stage)->first();
        $opportunity->load(['opportunityable','opportunityType','salesStage','opportunityStatus','opportunityOwner','opp_activities','stage_rating.stage','priority_level','comments.createdBy','attachments.createdBy','rfp' => ['rfpDetail.item','rfpDetail.brand','rfpDetail.assignToEmployee','rfpDetail.uom','rfpDetail.division','rfpDetail.erpItemCategory', 'rfpStatus','rfq'=>['rfqStatus','supplier','rfqDetail.uom','rfqDetail.item'],'quotation'=>['quotationDetail','supplier','customer','quotationStatus']]])->get();

        $opportunity->scale_rating = $scalerating ? $scalerating->rating : null;
        return resp(1, 'Successful!', $opportunity, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Opportunity $opportunity)
    {
        $this->authorizeAny([
            'opportunities_update',
        ]);
        $request->validate([
           // 'series' => 'required|string|max:255',
            'opportunity_type' => 'required|integer',
            //'sales_stage' => 'required|integer',
            'opportunityable_id' => 'required',
            'opportunityable_type' => 'required',
            'opportunity_status' => 'required',
            'closing_date' => 'date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $this->input['closing_date']=$request->closing_date ?? NULL;
            $this->input['opportunityable_type']= $request->opportunityable_type === 'lead' ? Lead::class : ($request->opportunityable_type === 'prospect' ? Prospect::class : Customer::class);
            $opportunity->update($this->input);
            $opportunity->refresh();

            DB::commit();
            return resp(1, 'Successful!', $opportunity->load('opportunityable','opportunityType','stage_rating.stage','priority_level','opp_activities','salesStage','opportunityStatus','opportunityOwner'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Opportunity $opportunity)
    {
        $this->authorizeAny([
            'opportunities_delete',
        ]);
        $opportunity->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
    function getEmployeeHierarchy($employee_id) {
        // Fetch the main employee details along with sales team details
        $employee = Employee::with('salesTeamEmployee')->find($employee_id);

        if (!$employee) {
            return null; // Return null if employee not found
        }

        // Fetch subordinates and recursively build their hierarchy
        $employee->subordinates = Employee::whereHas('salesTeamEmployee')
            ->where('report_to_id', $employee_id)
            ->get()
            ->map(function ($subordinate) {
                $subordinate->subordinates = $this->getEmployeeHierarchy($subordinate->id);
                return $subordinate;
            });

        return $employee;
    }
    function getAllEmployeesAtSameLevel($employee, &$result = []) {
        if (!$employee || !is_object($employee)) {
            return;
        }

        // Store the current employee in result
        $result[] = $employee;

        // Check if subordinates exist before looping
        if (!empty($employee->subordinates)) {
            foreach ($employee->subordinates as $subordinate) {
                $this->getAllEmployeesAtSameLevel($subordinate, $result);
            }
        }

        return $result;
    }




    public function getOpportunityDropdown()
    {
        //$data['employees']=Employee::query()->whereHas('salesTeamEmployee')->with('salesTeamEmployee')->get();
        $employeeHierarchy=$this->getEmployeeHierarchy(auth()->user()->employee_id);
        $allSameLevelEmployees = $this->getAllEmployeesAtSameLevel($employeeHierarchy);
        $data['employees']=$allSameLevelEmployees;
        $data['opportunity_from']=array('Prospect','Lead','Customer');
        $data['leads']=Lead::query()->get();
        $data['prospect']=Prospect::query()->get();
        $data['customer']=Customer::query()->get();
        $data['opportunity_type']=Type::getTypeValues('opportunity-type');
        $data['sales_stage']=Type::getTypeValues('sales-stage');
        $data['opportunity_status']=Type::getTypeValues('opportunity-status');
        $data['priority_level']=Type::getTypeValues('priority-level');
        $data['divisions']=Division::query()->with('divisionEmployee')->get();
        $data['ratings']=ScaleRating::query()->with('stage')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getOpportunityPipeline(Request $request)
    {
        $request->validate([
            'opportunity_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();


            DB::commit();
            //return resp(1, 'Successful!',, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function updateStageRating(Request $request)
    {
        $request->validate([
            'opportunity_id' => 'required|integer',
            'stage_rating' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $opportunity=Opportunity::query()->find($request->opportunity_id);
            if(!empty($opportunity)){
                $opportunity->update(['stage_rating'=>$request->stage_rating]);
                $opportunity->refresh();
            }
            DB::commit();
            return resp(1, 'update Successful!', $opportunity, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
