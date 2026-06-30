<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Admin\Inventory;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookIssued;
use App\Models\Admin\Library\BookRequest;
use App\Models\Admin\Location;
use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookIssuedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'book_issued_view'
        ]);

        $data = BookIssued::with(['BookId.book_type','EmployeeId' => ['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'],'created_by','updated_by'])->orderByDesc('id')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'book_issued_create'
        ]);

        $request->validate([
            'book_id' => 'required',
            'employee_id' => 'required',
            'issued_date' => 'required',
            'due_date' => 'required',
            //'book_request_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $book = Book::find($request->input('book_id'));
            $issuedCount = BookIssued::where('book_id', $request->input('book_id'))->count();

            if ($issuedCount >= $book->book_qty) {
                return resp('0', 'The book is not available.', '', Response::HTTP_BAD_REQUEST);
            }
            $item = BookIssued::query()->create($this->input);
            if($item){
                BookRequest::query()->where('id',$request->book_request_id)->update(array('status'=>2));
            }
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
    public function show(BookIssued $bookIssued): JsonResponse
    {
        $this->authorizeAny([
            'book_issued_view'
        ]);

        $bookIssued = $bookIssued->load(['BookId.book_type','EmployeeId' => ['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'],'created_by','updated_by'])->get();
        return resp('1', 'Successful!', $bookIssued, Response::HTTP_OK);
    }

    public function getEmployeeIssuedBooks($empId): JsonResponse
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $bookIssued = BookIssued::query()->with(['BookId.book_type','EmployeeId' => ['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'],'created_by','updated_by'])->where('employee_id', $empId)->get();
        return resp('1', 'Successful!', $bookIssued, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookIssued $bookIssued)
    {
        $this->authorizeAny([
            'book_issued_update'
        ]);

        $request->validate([
            'book_id' => 'required',
            'employee_id' => 'required',
            'issued_date' => 'required',
            'due_date' => 'required',
            //'book_request_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $bookIssued->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function returnBooks(Request $request){
        $request->validate([
            'id' => 'required',
            'return_date' => 'required',
            'fine_amount' => 'required',
        ]);
        $bookIssued = BookIssued::query()->findOrFail($this->input['id']);
        try {
            $this->input['status'] = 2;
            DB::beginTransaction();
            $item = $bookIssued->update($this->input);
            DB::commit();
            return resp('1', 'Book Return Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getLibraryDropDown(){
        $data['location']= Location::all();
        $data['employees']= Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['books']= Book::with('Reconciliation.BookReconciliationId.ReconciliationType')->get();
        $data['book_type']= Type::getTypeValues('book-type');
        $data['book_category']= Type::getTypeValues('book-category');
        $data['reconciliation_type']= Type::getTypeValues('reconciliation-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookIssued $bookIssued): JsonResponse
    {
        $this->authorizeAny([
            'book_issued_delete'
        ]);

        $item = $bookIssued->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
