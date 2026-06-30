<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $shift=Shift::query()->with(['shiftDetail','employees' => ['department','designation','branchOffice']])->get();
        return resp(1,'Successful!', $shift,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'shift_name' => [
                'required',
                Rule::unique('shifts')->whereNull('deleted_at'),
            ],
            'shift_next_day_end' => 'required|integer',
            'status' => 'required|integer',
            'shift_details' => 'required|array',
            'shift_details.*.shift_day' => 'required|string',
            //'shift_details.*.shift_start_time' => 'required',
            //'shift_details.*.shift_end_time' => 'required',
            'employee_ids' => 'nullable|array',
        ]);
        try {
            DB::beginTransaction();
            $shifts_detail = $request->shift_details;
            unset($this->input['shift_details']);
            $shift = Shift::query()->create($this->input);
            if ($shift) {
                foreach ($shifts_detail as $detail) {
                    $shiftdetail = array(
                        'shift_id' => $shift->id,
                        'shift_day' => $detail['shift_day'],
                        'shift_start_time' =>($detail['shift_start_time'] !="")? date('H:i:s', strtotime($detail['shift_start_time'])):"",
                        'shift_end_time' => ($detail['shift_end_time'] !="")?date('H:i:s', strtotime($detail['shift_end_time'])):"",
                        'is_WH' => $detail['is_WH'],
                        'isDayOff' => $detail['isDayOff'],
                    );
                    ShiftDetail::query()->create($shiftdetail);
                }

                if ($request->employee_ids && count($request->employee_ids) > 0)
                    Employee::query()->whereIn('id', $request->employee_ids)->update(['shift_id' => $shift->id]);
            }
            DB::commit();
            $data['shift_detail'] = $shift->load('shiftDetail');
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        }catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        return resp(1,'Successful!', $shift->load(['shiftDetail','employees' => ['department','designation','branchOffice']]),Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'shift_name' => [
                'required',
                Rule::unique('shifts')->whereNull('deleted_at')->ignore($shift->id),
            ],
            'shift_next_day_end' => 'required|integer',
            'status' => 'required|integer',
            'shift_details' => 'required|array',
            'shift_details.*.shift_day' => 'required|string',
            //'shift_details.*.shift_start_time' => 'required',
            //'shift_details.*.shift_end_time' => 'required',
            'employee_ids' => 'nullable|array',
        ]);
        try {
            DB::beginTransaction();
            $shifts_detail = $request->shift_details;
            unset($this->input['shift_details']);
            $updateShift = Shift::query()->find($shift->id)->update($this->input);
            if ($updateShift) {
                ShiftDetail::query()->where('shift_id',$shift->id)->delete();
                foreach ($shifts_detail as $detail) {
                    $shiftdetail = array(
                        'shift_id' => $shift->id,
                        'shift_day' => $detail['shift_day'],
                        'shift_start_time' =>($detail['shift_start_time'] !="")? date('H:i:s', strtotime($detail['shift_start_time'])):"",
                        'shift_end_time' => ($detail['shift_end_time'] !="")?date('H:i:s', strtotime($detail['shift_end_time'])):"",
                        'is_WH' => $detail['is_WH'],
                        'isDayOff' => $detail['isDayOff'],
                    );
                    ShiftDetail::query()->create($shiftdetail);
                }

                if ($request->employee_ids && count($request->employee_ids) > 0)
                    Employee::query()->whereIn('id', $request->employee_ids)->update(['shift_id' => $shift->id]);
            }
            DB::commit();
            $data['shift_detail'] = $shift->load('shiftDetail');
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        }catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $shift->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }

    public function shiftDropdown()
    {
        $data['days']=['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $data['employees'] = Employee::with(['department','designation','branchOffice'])->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}
