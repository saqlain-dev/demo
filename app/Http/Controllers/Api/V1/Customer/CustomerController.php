<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAttachment;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'crm_customer_view',
        ]);
        $data['customer']=Customer::query()->with('customerType','country','customerContact','comments.createdBy','attachments.createdBy')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'crm_customer_create',
        ]);
        $request->validate([
            'customer_name' => 'required',
            'customer_type' => 'required',
            'customer_email' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if($request->hasFile('profile')) {

                $responce = $this->saveFile($request, 'CustomerProfile');

                if ($responce) {
                    $this->input['profile'] = $responce;
                }
            }
            $customer=Customer::query()->create($this->input);


            DB::commit();
            return resp(1, 'Successful!', $customer->load('customerType','country','comments','attachments'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveFile($request,$folder){

        $file = $request->file('profile');
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
        return $path.'/'.$file_name;

    }
    public function saveAttachment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'customer_attachment' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if ($request->hasFile('customer_attachment')) {
                $responses = $this->saveAttachmentFile($request, 'CustomerAttachment');

                $this->input['customer_attachment'] = $responses;
            }
            $CustomerAttachment=CustomerAttachment::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $CustomerAttachment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function deleteAttachment(Request $request)
    {
        $request->validate([
            'attachment_id' => 'required',
        ]);

        try {

            $CustomerAttachment = CustomerAttachment::query()->find($request->attachment_id);

            if ($CustomerAttachment) {
                $CustomerAttachment->delete();
            } else {
                // Handle the case when the attachment is not found
                return resp(0, 'Attachment not found!', [], Response::HTTP_OK);
            }


            return resp(1, 'Successful!', $CustomerAttachment, Response::HTTP_OK);
        } catch (\Exception $e) {

            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $cr_customer)
    {
        $this->authorizeAny([
            'crm_customer_view',
        ]);

        return resp(1, 'Successful!', $cr_customer->load(['customerType','country','comments.createdBy','customerQuotation.purchaseOrder','customerQuotation.quotationStatus','attachments.createdBy','customerContact' => ['customerContactStatus','salutation','gender']]), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $cr_customer)
    {
        $this->authorizeAny([
            'crm_customer_update',
        ]);
        $request->validate([
            'customer_name' => 'required',
            'customer_type' => 'required',
            'customer_email' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if($request->hasFile('profile')) {

                $responce = $this->saveFile($request, 'CustomerProfile');

                if ($responce) {
                    $this->input['profile'] = $responce;
                }
            }
            $cr_customer->update($this->input);
            $cr_customer->refresh();

            DB::commit();
            return resp(1, 'Successful!', $cr_customer->load('customerType','country','comments','attachments'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $cr_customer)
    {
        $this->authorizeAny([
            'crm_customer_delete',
        ]);
        $cr_customer->customerContact()->delete();
        $cr_customer->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
    public function getCustomerDropdowns()
    {
        $data['customer_type']=Type::getTypeValues('customer-type');
        $data['countries']=DB::table('countries')->get();
        $data['customer_contact_status']=Type::getTypeValues('customer-contact-status');
        $data['salutation']=Type::getTypeValues('salutation');
        $data['gender']=Type::getTypeValues('gender');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function saveAttachmentFile($request, $folder)
    {
        $image = $request->file('customer_attachment');

        $path = 'uploads/media/' . $folder;

        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $filename = time() . '_' . $image->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $image->move($path, $file_name);

        $path = $path . '/' . $file_name;

        return $path;
    }
}
