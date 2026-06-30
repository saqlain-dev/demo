<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Procurement;
use App\Models\Admin\ProcurementDetail;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\BranchOffice;
use App\Models\EventManagement;
use App\Models\EventManagementVendor;
use App\Models\EventMangementInvoice;
use App\Models\Invoice;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventManagementController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all events
        $events = EventManagement::with(['branchOffice', 'procurement', 'procurementDetail', 'category', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return resp('1', 'Record Fetched Successfully!', $events, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'sometimes|exists:type_values,id',
            'procurement_id' => 'sometimes|exists:procurements,id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        // Convert "2025-08-22T12:03" → "2025-08-22 12:03:00"
        $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->start_date)->format('Y-m-d H:i:s');
        $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->end_date)->format('Y-m-d H:i:s');

        $request->merge([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Create a new event
        $event = EventManagement::create($request->all());
        $event->load(['category', 'eventMangementDetails', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Created Successfully!', $event, Response::HTTP_CREATED);
    }

    // Show
    public function show($id)
    {

        $event = EventManagement::with(
            [
                'procurement.items.item' => [
                    'itemCategory',
                    'itemType',
                    'itemUnit'
                ],
                'procurementDetail.item' => [
                    'itemCategory',
                    'itemType',
                    'itemUnit'
                ],
                'category',
                'eventMangementDetails',
                'eventMangementDetails.procurementDetails',
                'eventMangementDetails.procurementDetails.item',
                'eventMangementDetails.roomType',
                'eventMangementDetails.seatingArrangement',
                'eventMangementDetails.boardType',
                'eventManagementReqVendor.vendorDetail',
                'eventManagementquotations.vendorDetail',
                'invoice',
                'eventTasks.assignTo',
                'eventTasks.flagStatus',
                'eventTasks.taskStatus',
                'eventCommunications',
                'createdBy',
                'updatedBy',
                'branchOffice'
            ]
        )->find($id);

        if (!$event) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }

        $data['event'] = $event;
        $data['approval_request'] = getNextApproval(75, auth()->user()->designation_id, $id);
        $data['approval_request_status'] = checkApprovalRequestStatus(75, $id);


        return resp('1', 'Record Fetched Successfully!', $data, Response::HTTP_OK);
    }

    // Update
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'sometimes|exists:type_values,id',
            'procurement_id' => 'sometimes|exists:procurements,id',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $event = EventManagement::find($id);
        // Convert "2025-08-22T12:03" → "2025-08-22 12:03:00"
        $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->start_date)->format('Y-m-d H:i:s');
        $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->end_date)->format('Y-m-d H:i:s');

        $request->merge([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        if (!$event) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }

        $event->update($request->all());
        $event->load(['category', 'eventMangementDetails', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Updated Successfully!', $event, Response::HTTP_OK);
    }

    // Delete
    public function destroy($id)
    {
        $event = EventManagement::find($id);

        if (!$event) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }

        $event->delete();

        return resp('1', 'Record Deleted Successfully!', null, Response::HTTP_OK);
    }

    //dropdown
    public function getEventManagementDropdowns()
    {
        $data['event_categories'] = Type::getTypeValues('event-categories');
        $data['branch_offices'] = BranchOffice::all();
        $data['roomTypes'] = Type::getTypeValues('event-rooms');
        $data['seatingArrangements'] = Type::getTypeValues('seating-arrangements');
        $data['boardTypes'] = Type::getTypeValues('board-types');
        $data['procurements'] = Procurement::with('items', 'items.item', 'items.itemCategory')->get();

        return resp('1', 'Dropdowns Fetched Successfully!', $data, Response::HTTP_OK);
    }

    //Attach Vendors
    public function attachEventVendor(Request $request, EventManagement $event)
    {

        if ($event->approval_status == 1) {

            $request->validate([
                'vendors' => 'required|array|min:1',
                'vendors.*.vendor' => 'required|exists:vendors,id',
                'vendors.*.type' => 'required', // adjust table/column as needed
            ]);

            try {
                DB::beginTransaction();
                $vendors = $request->vendors;
                unset($this->input['vendors']);
                $this->input['float_vendor'] = 1;
                $eventUpdate = EventManagement::query()->where('id', $event->id)->update($this->input);
                if ($eventUpdate) {
                    foreach ($vendors as $vendor) {
                        EventManagementVendor::query()->create(
                            [
                                'event_management_id' => $event->id,
                                'vendor_id' => $vendor['vendor'],
                                'type' => $vendor['type'],
                            ]
                        );
                    }
                }

                DB::commit();
                $event = EventManagement::query()->findOrFail($event->id);
                return resp(1, 'Successful!', $event, Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
            }
        } else {
            return resp(0, 'Event not approved yet.', [], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    //invoice
    public function invoice(Request $request)
    {
        $data = $request->validate([
            // Invoice fields
            'invoice_date' => 'required|date',
            'invoice_amount' => 'required|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'event_management_id' => 'required|exists:event_management,id',
            'supplier_id' => 'required|exists:vendors,id',
            // Vehicle details
            'details' => 'required|array|min:1',
            'details.*.venue_name' => 'nullable|string',
            'details.*.event_management_details_id' => 'nullable|exists:event_management_details,id',
            'details.*.vendor_id' => 'nullable',
            'details.*.total_seats' => 'nullable',
            'details.*.fair_per_day' => 'nullable|string',
            'details.*.days' => 'nullable|numeric',
            'details.*.total_amount' => 'nullable',
            'details.*.total_rooms' => 'nullable',
            'details.*.remarks' => 'nullable',
            'details.*.address' => 'nullable',
            'details.*.quotation_id' => 'nullable|exists:vendor_event_managment_quotations,id',
        ]);
        DB::beginTransaction();
        try {
            $invoiceFilePath = null;
            if ($request->hasFile('invoice_file')) {
                $invoiceFilePath = $this->saveFile($request, 'invoice_file');
            }

            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO = 'INV/' . sprintf('%04d', $statement[0]->nextID);
            // Create Invoice
            $inputDatetime = $data['invoice_date'];
            $invoice = Invoice::create([
                'invoice_number' => $inVNO,
                'invoice_date' => Carbon::createFromFormat('Y-m-d\TH:i', $inputDatetime)->format('Y-m-d H:i:s'),
                'supplier_id' => $data['supplier_id'],
                'invoice_amount' => $data['invoice_amount'],
                'invoice_file' => $invoiceFilePath,
                'created_by' => Auth::id(),
                'invoice_status' => 4,
                'event_management_id' => $request->event_management_id
            ]);

            // Create associated details
            $details = [];
            foreach ($data['details'] as $item) {
                $item['invoice_id'] = $invoice->id;
                $item['event_management_id'] = $request->event_management_id;
                $details[] = EventMangementInvoice::create($item);
            }

            DB::commit();
            return resp('1', 'Invoice and details created successfully!', [
                'invoice' => $invoice->load(['invoiceEventManagementDetail' => ['quotation', 'eventManagement.eventMangementDetails']]),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create invoice and details', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveFile($request, $folder)
    {
        $file = $request->file('invoice_file');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path . '/' . $file_name;

    }
    public function sendEventManagementForApproval(EventManagement $item)
    {

        $approval_process_name = ApprovalProcessName::query()->where('id', 75)->first();
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 75)->get();
        $checkProcess = ApprovalProcessList::query()->where('approval_process_id', 75)->where('approval_request_status', 1)->where('request_module_id', $item->id)->count();
        if ($approval_process->count() > 0 && $checkProcess == 0) {

            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $item->id,
                );
                $Approval = ApprovalProcessList::query()->create($insert);

                sendNotification($approval['designation_id'], $approval_process_name->approval_process_name);

            }
            $update = array('approval_status' => 2);
            EventManagement::query()->where('id', $item->id)->update($update);
            return resp(1, 'Event Management Request send for Approval.', $Approval, Response::HTTP_OK);
        } else {


            if ($checkProcess == 0) {
                return resp(0, 'Approval process not available', [], Response::HTTP_OK);
            } else {
                return resp(0, 'Event Management Request approval already sent.', [], Response::HTTP_OK);
            }
        }
    }



}
