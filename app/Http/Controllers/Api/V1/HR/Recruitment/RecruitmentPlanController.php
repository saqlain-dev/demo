<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\HR\Recruitment\RecruitmentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RecruitmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'recruitment_plan_view',
            'consultant_recruitment_plan_view',
        ]);

        $data = RecruitmentPlan::with(['BudgetId','created_by','updated_by','RecruitmentPlanDetail.BudgetDetailId.Head'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getConsultantRecruitmentPlan()
    {
        $data['consultant_plans'] = RecruitmentPlan::query()->with(['BudgetId','created_by','updated_by','RecruitmentPlanDetail.BudgetDetailId.Head'])->where('IsConsultant',1)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'recruitment_plan_create',
            'consultant_recruitment_plan_create',
        ]);

        $request->validate([
            'budget_id' => 'required|integer|unique:recruitment_plans,budget_id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = RecruitmentPlan::query()->create($this->input);
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
    public function show($id): JsonResponse
    {
        $this->authorizeAny([
            'recruitment_plan_view',
            'consultant_recruitment_plan_view',
        ]);

//        $this->authorizeAny([
//            'log_book_view'
//        ]);
        $recruitmentPlan = RecruitmentPlan::query()->with(['BudgetId.BudgetDetail' => ['UnitType','Head' => ['parent','AccountTypeId']], 'created_by', 'updated_by','RecruitmentPlanDetail.BudgetDetailId'=>['UnitType','Head'], 'RecruitmentPlanDetail.employeeRequisition'])->findOrFail($id);
       
        $budgetDetails = $recruitmentPlan->BudgetId->BudgetDetail->toArray();
        //dd($recruitmentPlan->BudgetId);
        $recruitmentPlan->BudgetId->BudgetDetail->each(function ($budgetDetail) {
            if ($budgetDetail->Head) {
                $budgetDetail->Head->loadAllParents();
            }
            //dd($budgetDetail->Head);
        });
        //dd($budgetDetails);
        // Create a flat list of heads
        $headList = [];
        foreach ($budgetDetails as &$detail) {
            //dd($detail);
            if (isset($detail['head'])) {
                $headList[$detail['head']['id']] = &$detail['head'];
            }

        }
        //dd($headList);


        // Build the tree from the flat list
        $tree = $this->buildTree($headList);
        // Include the tree in the response
        $data['budgetDetailsTree'] = $tree;
        $data['recruitmentPlan'] = $recruitmentPlan;
        //dd($tree);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    public function buildTree(&$headList)
    {
        $tree = [];
        $references = [];

        // Create references for each node
        foreach ($headList as &$head) {
            $references[$head['id']] = &$head;
            $head['children'] = [];
        }

        // Build the tree by linking children to their parents
        foreach ($headList as &$head) {
            if ($head['parent_id'] == 0 || $head['parent_id'] === null) {
                // Root node
                $tree[] = &$head;
            } else {
                // Ensure parent exists in references before linking
                if (isset($references[$head['parent_id']])) {
                    $references[$head['parent_id']]['children'][] = &$head;
                } else {
                    // If parent doesn't exist, consider as root node
                    $tree[] = &$head;
                }
            }
        }

        return $tree;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecruitmentPlan $recruitmentPlan)
    {
        $this->authorizeAny([
            'recruitment_plan_update',
            'consultant_recruitment_plan_update',
        ]);

        $request->validate([
            'budget_id' => 'required|integer|unique:recruitment_plans,budget_id,'.$recruitmentPlan->id,
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $recruitmentPlan->update($this->input);
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
    public function destroy(RecruitmentPlan $recruitmentPlan): JsonResponse
    {
        $this->authorizeAny([
            'recruitment_plan_delete',
            'consultant_recruitment_plan_delete',
        ]);

//        $this->authorizeAny([
//            'log_book_delete'
//        ]);
        $recruitmentPlan->RecruitmentPlanDetail()->delete();
        $item = $recruitmentPlan->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
