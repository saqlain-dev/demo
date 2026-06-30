<?php

namespace App\Http\Controllers\Api\V1\HR\Payscale;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\HR\Payscale\Payscale;
use App\Models\HR\Payscale\PayscaleGrading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PayscaleGradingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'payscale_view',
            'manage_audit_payscale',
        ]);

        $data['payscale_grading'] = PayscaleGrading::with(['created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'grading' => 'required',
            'amount' => 'required|numeric',
        ]);
        try {
            DB::beginTransaction();
            $checkAlreadyExist=PayscaleGrading::query()->where('grading',$request->grading)->where('amount',$request->amount)->count();
            if($checkAlreadyExist == 0){
                $item = PayscaleGrading::query()->create($this->input);
                $data['payscale_grading']=$item;
                DB::commit();
                return resp(1, 'Record Created Successfully!', $data, Response::HTTP_CREATED);
            }else{
                return resp(0, 'Grading already added.', [], Response::HTTP_EXPECTATION_FAILED);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PayscaleGrading $payscaleGrading): JsonResponse
    {
        $this->authorizeAny([
            'payscale_view',
            'manage_audit_payscale',
        ]);
        $payscale = $payscaleGrading;
        return resp('1', 'Successful!', $payscale, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayscaleGrading $payscale_grading)
    {
        $request->validate([
            'grading' => 'required',
            'amount' => 'required|numeric',
        ]);
        try {
            DB::beginTransaction();
            $payscale_grading->grading=$this->input['grading'];
            $payscale_grading->amount=$this->input['amount'];
            $payscale_grading->save();
            $payscale_grading->refresh();
            $data['payscale_grading']=$payscale_grading;
            DB::commit();
            return resp(1, 'Record updated Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayscaleGrading $payscaleGrading)
    {
        $this->authorizeAny([
            'payscale_delete',
        ]);

        $item = $payscaleGrading->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
