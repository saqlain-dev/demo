<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Procurement;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function getProcurementPlan(Request $request)
    {

        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'status' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $startDate=date('Y-m-d',strtotime($request->start_date));
            $endDate=date('Y-m-d',strtotime($request->end_date));
            $procurement_plan = Procurement::with(['items' => ['project', 'item','selectionMethod','amountType','procurementMethod','qualificationType'],'budget.BudgetDetail'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('approval_status', $request->status);
            })
            ->whereBetween(\DB::raw('CONVERT(date, created_at)'), [$startDate, $endDate])->orderByDesc('id')->get();
            $procurement_plan->each(function ($record) {
                $record->district_details = District::query()->whereIn('id', $record->districts)->get();
            });
            $data['procurement_plan']=$procurement_plan;
            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}
