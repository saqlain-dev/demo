<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\CommunicationEventDetail;
use App\Models\Communication\EventCategory;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CommunicationEventDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CommunicationEventDetail::query()->with('department','subCategory','category','eventDetailHistory')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'category_id' => 'required|integer',
            //'sub_category_id' => 'required|integer',
            'department_id' => 'required|integer',
            'event_name' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'color_scheme' => 'required|string|max:255',
            'quantity' => 'required|integer',
            //'other_requirements' => 'required',
            'budget' => 'required|integer',
            'content'=> 'nullable',
            'attachment' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            if ($request->hasFile('attachment')) {
                $responses = $this->saveAttachmentgFile($request, 'CommunicationEvent');

                $this->input['attachment'] = $responses;
            }

            $item = CommunicationEventDetail::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachmentgFile($request, $folder)
    {
        $image = $request->file('attachment');

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

    /**
     * Display the specified resource.
     */
    public function show(CommunicationEventDetail $communicationEventDetail)
    {
        $data['item'] = $communicationEventDetail->load('department','subCategory','category', 'comments.createdBy','eventDetailHistory');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunicationEventDetail $communicationEventDetail)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'category_id' => 'required|integer',
            //'sub_category_id' => 'required|integer',
            'department_id' => 'required|integer',
            'event_name' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'color_scheme' => 'required|string|max:255',
            'quantity' => 'required|integer',
            //'other_requirements' => 'required',
            'budget' => 'required|integer',
            'content'=> 'required|string|max:255',
            'attachment' => 'nullable',
        ]);
        try {
            DB::beginTransaction();

            if ($request->hasFile('attachment')) {
                $responses = $this->saveAttachmentgFile($request, 'CommunicationEvent');

                $this->input['attachment'] = $responses;
            }

            $parent = $communicationEventDetail->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $communicationEventDetail, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunicationEventDetail $communicationEventDetail)
    {
        $communicationEventDetail->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['categories'] =  EventCategory::with('subCategories')->get();
        $data['departments'] =  Type::getTypeValues('department-names');

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
}
