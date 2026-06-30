<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookIssued;
use App\Models\Admin\Library\BookRequest;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'book_requests_view'
        ]);

        $data = BookRequest::query()->where('status',1)->with(['BookId.book_type','EmployeeId' ,'created_by','updated_by'])->orderByDesc('id')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'book_requests_create'
        ]);

        $request->validate([
            'book_id' => 'required',
            'employee_id' => 'required',
        ]);
        try {
            $existingRequest = BookRequest::where('book_id', $request->input('book_id'))
                ->where('employee_id', $request->input('employee_id'))
                //->where('issued_date', $request->input('issued_date'))
                ->first();
            if ($existingRequest) {
                return resp('0', 'This book has already been requested by the same employee.', '', Response::HTTP_BAD_REQUEST);
            }

            // Check if the book is available
            $book = Book::find($request->input('book_id'));
            $issuedCount = BookIssued::where('book_id', $request->input('book_id'))->count();

            if ($issuedCount >= $book->book_qty) {
                return resp('0', 'The book is not available.', '', Response::HTTP_BAD_REQUEST);
            }
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('book_requests') as nextID");
            $bookRequestNo='bkr/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['book_request_no']=$bookRequestNo;
            $item = BookRequest::query()->create($this->input);
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
    public function show(BookRequest $bookRequest): JsonResponse
    {
        $this->authorizeAny([
            'book_requests_view'
        ]);
        $data['bookRequest']=$bookRequest = $bookRequest->load(['BookId.book_type','EmployeeId' => ['designation','department'],'created_by','updated_by']);
        $data['approval_request']=getNextApproval(19,auth()->user()->designation_id,$bookRequest->id);
        $data['approval_request_status']=checkApprovalRequestStatus(19,$bookRequest->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getEmployeeRequestedBooks($empId): JsonResponse
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $data['items'] = BookRequest::query()->with(['BookId.book_type','EmployeeId','created_by','updated_by'])->where('employee_id', $empId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getRequestedBooksByRequestNo($reqNo): JsonResponse
    {
        $data['requestedBook'] = BookRequest::query()->with(['BookId.book_type','EmployeeId','created_by','updated_by'])->where('book_request_no', $reqNo)->first();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookRequest $bookRequest)
    {
        $this->authorizeAny([
            'book_requests_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'book_id' => 'required',
            'employee_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $bookRequest->update($this->input);
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
    public function destroy(BookRequest $bookRequest): JsonResponse
    {
        $this->authorizeAny([
            'book_requests_delete'
        ]);

        $item = $bookRequest->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendBookRequestForApproval(BookRequest $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',19)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',19)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

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
            BookRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Book request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Book request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
