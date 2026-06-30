<?php

namespace App\Http\Controllers\Api\V1\HR\Leaves;

use App\Http\Controllers\Controller;
use App\Models\HR\Leaves\YearlyLeaveEntitlement;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class YearlyLeaveEntitlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['leaveList']=YearlyLeaveEntitlement::query()->with('leave_type')->orderBy('id','DESC')->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
    public function leaveDropDown()
    {
        $data['leave_types']=Type::getTypeValues('leave-type');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'leave_type_id' => 'required|integer',
                'yearly_entitlement_year' => 'required|date_format:Y',
                'yearly_entitlement_leave' => 'required|integer',
            ]);
            $leaveTypeAddedAlready=YearlyLeaveEntitlement::query()->where('leave_type_id',$this->input['leave_type_id'])->where('yearly_entitlement_year',$this->input['yearly_entitlement_year'])->count();
            if($leaveTypeAddedAlready == 0){
                $leave=YearlyLeaveEntitlement::query()->create($this->input);
                DB::commit();
                return resp('1', 'Yearly leave entitlement added Successfully!', $leave->load('leave_type'), Response::HTTP_CREATED);
            }else{
                return resp('0', 'Yearly leave entitlement already added against this leave type.', [], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(YearlyLeaveEntitlement $leaveEntitlement)
    {
        return resp('1', 'Record deleted Successfully!', $leaveEntitlement, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'leave_type_id' => 'required|integer',
                'yearly_entitlement_year' => 'required|date_format:Y',
                'yearly_entitlement_leave' => 'required|integer',
            ]);
            $leave=YearlyLeaveEntitlement::query()->findOrFail($id);
            $leave->update($this->input);
                DB::commit();
            $leave->refresh();
            return resp('1', 'Yearly leave entitlement updated Successfully!', $leave->load('leave_type'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(YearlyLeaveEntitlement $leaveEntitlement)
    {
        $leaveEntitlement->delete();
        return resp('1', 'Record deleted Successfully!', [], Response::HTTP_OK);
    }
}
