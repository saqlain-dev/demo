<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookReconciliation;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookReconciliationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'books_reconciliation_view'
        ]);

        $data['BookReconciliation'] = BookReconciliation::with(['ReconciliationType','created_by','updated_by','ReconciliationDetail.BookId'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'books_reconciliation_create'
        ]);

        $request->validate([
            'reconciliation_type' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = BookReconciliation::query()->create($this->input);
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
    public function show($id): JsonResponse
    {
        $this->authorizeAny([
            'books_reconciliation_view'
        ]);

        $bookReconciliation = BookReconciliation::query()->findOrFail($id);
        $data['book'] = $bookReconciliation->load(['ReconciliationType','created_by','updated_by','ReconciliationDetail.BookId']);
        $data['approval_request']=getNextApproval(34,auth()->user()->designation_id,$bookReconciliation->id);
        $data['approval_request_status']=checkApprovalRequestStatus(34,$bookReconciliation->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $this->authorizeAny([
            'books_reconciliation_update'
        ]);

        $bookReconciliation = BookReconciliation::query()->findOrFail($id);
        $request->validate([
            'reconciliation_type' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $bookReconciliation->update($this->input);
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
    public function destroy($id): JsonResponse
    {
        $this->authorizeAny([
            'books_reconciliation_delete'
        ]);

        $bookReconciliation = BookReconciliation::query()->with('ReconciliationDetail')->findOrFail($id);
        $bookReconciliation->ReconciliationDetail()->delete();
        $item = $bookReconciliation->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendBookReconciliationForApproval(BookReconciliation $item)
    {


        $approval_process=ApprovalProcess::query()->where('approval_process_id',34)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',34)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            BookReconciliation::query()->where('id',$item->id)->update($update);
            return resp(1,'Book reconciliation send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Book reconciliation approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
