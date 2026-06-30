<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\RFQ\Rfq;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = Attachment::query()->with('createdBy', 'attachmentable')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'attachment' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'attachmentable_type' => 'required|string',
            'attachmentable_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $type = $this->getModel($request->attachmentable_type);

            if (!$type) {
                return resp(0, 'Invalid commentable type!', [], Response::HTTP_BAD_REQUEST);
            }

            if ($request->hasFile('attachment')) {
                $responce =$this->saveImages($request,'Attachment');
                if ($responce) {
                    $this->input['attachment']=$responce;
                }

            }
            $this->input['attachmentable_type'] = $type;
            $attachment = Attachment::query()->create($this->input);



            DB::commit();
            return resp(1, 'Successful!', $attachment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImages($request,$folder){

        $file = $request->file('attachment');
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

    /**
     * Display the specified resource.
     */
    public function show(Attachment $attachment)
    {
        $attachment->load('createdBy', 'attachmentable');
        return resp('1', 'Successful!', $attachment, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attachment $attachment)
    {
        $request->validate([
            'attachment' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);
        try {
            DB::beginTransaction();
            if ($request->hasFile('attachment')) {
                $responce =$this->saveImages($request,'Attachment');
                if ($responce) {
                    $this->input['attachment']=$responce;
                }

            }
            $attachment->update($this->input);

            $attachment->refresh();

            DB::commit();
            return resp(1, 'Successful!', $attachment->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        $attachment->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    protected function getModel($type)
    {
        $models = [
            'customer' => Customer::class,
            'lead' => Lead::class,
            'opportunity' => Opportunity::class,
            'rfq' => Rfq::class,
            // Add other models here
        ];

        return $models[$type] ?? null;
    }
}
