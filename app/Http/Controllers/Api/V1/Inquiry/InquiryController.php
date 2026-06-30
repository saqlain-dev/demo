<?php

namespace App\Http\Controllers\Api\V1\Inquiry;

use App\Http\Controllers\Controller;
use App\Models\Inquiry\Inquiry;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'inquiries_view',
        ]);
        $data['inquiry_listing']=Inquiry::query()->with('inquiryType','lead')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'inquiries_create',
        ]);
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_address' => 'required|email',
            'phone_number' => 'required|numeric',
            'company' => 'required|string',
            //'inquiry_type' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            //unset($this->input['inquiry_type']);
            $inquiry=Inquiry::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $inquiry->load('inquiryType'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry)
    {
        $this->authorizeAny([
            'inquiries_view',
        ]);
        return resp(1, 'Successful!', $inquiry->load('inquiryType','lead'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $this->authorizeAny([
            'inquiries_update',
        ]);
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_address' => 'required|email',
            'phone_number' => 'required|integer',
            'company' => 'required|string',
            //'inquiry_type' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            //unset($this->input['inquiry_type']);
            $inquiry->update($this->input);
            $inquiry->refresh();

            DB::commit();
            return resp(1, 'Successful!', $inquiry->load('inquiryType'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry)
    {
        $this->authorizeAny([
            'inquiries_delete',
        ]);
        $inquiry->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getInquiryDropdown()
    {
        $data['inquiry_type']=Type::getTypeValues('inquiry-type');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
