<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\RecruitmentPlan;
use App\Models\HR\Recruitment\RecruitmentPlanDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RecruitmentPlanDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RecruitmentPlanDetail::with(['BudgetDetailId.Head','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recruitment_plan_id' => 'required',
            'budget_detail_id' => 'required',
            //'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $this->input['date']=date('Y-m-d',strtotime($request->date));
            $item = RecruitmentPlanDetail::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function addRecruitmentPlanBulk(Request $request)
    {
        $request->validate([
            '*.recruitment_plan_id' => 'required|integer',
            '*.budget_detail_id' => 'required|integer',
            '*.is_budgeted' => 'required|integer',
        ]);

        try {

            DB::beginTransaction();

            $recordsToInsert = [];

            foreach ($request->all() as $record) {
                $exists = RecruitmentPlanDetail::query()
                    ->where('recruitment_plan_id', $record['recruitment_plan_id'])
                    ->where('budget_detail_id', $record['budget_detail_id'])
                    ->exists();

                if (!$exists) {
                    $recordsToInsert[] = $record;
                }
            }

            if (!empty($recordsToInsert)) {
                RecruitmentPlanDetail::query()->insert($recordsToInsert);
            }
            //RecruitmentPlanDetail::query()->insert($request->all()); // Bulk insert

            DB::commit();
            return resp('1', 'Record Created Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RecruitmentPlanDetail $recruitmentPlanDetail): JsonResponse
    {
//        $this->authorizeAny([
//            'log_book_view'
//        ]);

        $logBook = $recruitmentPlanDetail->load(['BudgetDetailId.Head', 'created_by', 'updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecruitmentPlanDetail $recruitmentPlanDetail)
    {
        $request->validate([
            'recruitment_plan_id' => 'required',
            'budget_detail_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $recruitmentPlanDetail->update($this->input);
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
    public function destroy(RecruitmentPlanDetail $recruitmentPlanDetail): JsonResponse
    {
//        $this->authorizeAny([
//            'log_book_delete'
//        ]);
        $item = $recruitmentPlanDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
