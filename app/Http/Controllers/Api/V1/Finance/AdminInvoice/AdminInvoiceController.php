<?php

namespace App\Http\Controllers\Api\V1\Finance\AdminInvoice;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\EmployeeOffboarding;
use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Finance\Grants\GrantProposal;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny(['admin_bill_view']);
        $data = AdminInvoice::with(['CategoryId', 'budgetId', 'headId', 'created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'admin_bill_create',
        ]);

        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'attachment' => 'required',
            'total_amount' => 'required',
            //'remarks' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'admin_bill');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = AdminInvoice::query()->create($this->input);
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
            'admin_bill_view',
            'manage_employee_portal'
        ]);

        $adminInvoice = AdminInvoice::query()->findOrFail($id);
        $data['admin_bill'] = $adminInvoice->load(['CategoryId', 'budgetId', 'headId', 'created_by.employeeDetail.designation', 'created_by.employeeDetail.branchOffice', 'updated_by']);
        $data['approval_request'] = getNextApproval(50, auth()->user()->designation_id, $id);
        $data['approval_request_status'] = checkApprovalRequestStatus(50, $id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizeAny(['admin_bill_update']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'total_amount' => 'required|numeric',
            // 'attachment' => 'nullable|file'
        ]);

        try {
            DB::beginTransaction();

            // ✅ Fetch record FIRST
            $admin = AdminInvoice::findOrFail($id);

            // ✅ Handle attachment AFTER fetching record
            if ($request->hasFile('attachment')) {

                // delete old file safely
                if ($admin->attachment && file_exists(public_path($admin->attachment))) {
                    unlink(public_path($admin->attachment));
                }

                // save new file
                $validated['attachment'] = $this->saveImage($request, 'admin_bill');
            }

            // ✅ Update record
            $admin->update($validated);

            DB::commit();

            return resp(
                1,
                'Record Updated Successfully!',
                $admin->fresh(), // optional but better (returns updated model)
                Response::HTTP_OK // 200 is correct for update
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return resp(
                0,
                'Failed to update record!',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine()
                ],
                Response::HTTP_EXPECTATION_FAILED
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminInvoice $adminInvoice): JsonResponse
    {
        $this->authorizeAny([
            'admin_bill_delete',
        ]);

        $item = $adminInvoice->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendAdminBillForApproval(AdminInvoice $item)
    {

        $approval_process_name = ApprovalProcessName::query()->where('id', 50)->first();
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 50)->get();
        $checkProcess = ApprovalProcessList::query()->where('approval_process_id', 50)->where('approval_request_status', 1)->where('request_module_id', $item->id)->count();
        if ($approval_process->count() > 0 && $checkProcess == 0) {

            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $item->id,
                );
                if ($approval_process_name->isFinancialApproval == 1) {
                    if ($approval->financialAmount < $item->total_amount) {
                        $insert['approval_status'] = 0;
                        $Approval = ApprovalProcessList::query()->create($insert);
                    } else {
                        $Approval = ApprovalProcessList::query()->create($insert);
                    }
                } else {
                    $Approval = ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'], $approval_process_name->approval_process_name);

            }
            $update = array('approval_status' => 2);
            AdminInvoice::query()->where('id', $item->id)->update($update);
            return resp(1, 'Employee Off Boarding send for Approval.', $Approval, Response::HTTP_OK);
        } else {
            if ($checkProcess == 0) {
                return resp(0, 'Approval process not available', [], Response::HTTP_OK);
            } else {
                return resp(0, 'Employee Off Boarding approval already sent.', [], Response::HTTP_OK);
            }
        }
    }

    public function getDropdown()
    {
        $data['units'] = Type::getTypeValues('invoice-units');
        $data['project_budget_unit_type'] = Type::getTypeValues('project-budget-unit-type');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function saveImage($request, $folder)
    {
        $file = $request->file('attachment');
        $storagePath = 'uploads/media/' . $folder;

        // create directories if not exist
        if (!file_exists(public_path($storagePath))) {
            mkdir(public_path($storagePath), 0777, true);
        }

        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(public_path($storagePath), $filename);

        return $storagePath . '/' . $filename; // save this in DB
    }
}
