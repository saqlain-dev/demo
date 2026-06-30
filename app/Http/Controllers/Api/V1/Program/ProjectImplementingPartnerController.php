<?php

namespace App\Http\Controllers\Api\V1\Program;

use App\Http\Controllers\Controller;
use App\Models\HR\Policy;
use App\Models\Program\ProjectImplementingPartner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProjectImplementingPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorizeAny([
            'implementing_partners_view',
            'manage_audit_grant_management',
        ]);

        $data = ProjectImplementingPartner::with('OrgType')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAny([
            'implementing_partners_create',
        ]);

        //return $request->all();
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'postal_address' => 'required',
            'focal_person' => 'nullable',
            'focal_person_email' => 'nullable',
            'focal_person_contact' => 'nullable',
            'donor_email' => 'nullable',
            'website_link' => 'nullable',
            'org_type' => 'nullable',
        ]);

        if($request->file('logo')){
            $responce=$this->saveImage($request,'implementing_partner_logo');
            $this->input['logo']=$responce;
        }
        $item = ProjectImplementingPartner::query()->create($this->input);

        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    public function saveImage($request,$folder){

        $file = $request->file('logo');
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
    public function show(ProjectImplementingPartner $ProjectImplementingPartner): JsonResponse
    {
        $this->authorizeAny([
            'implementing_partners_view',
            'manage_audit_grant_management',
        ]);

        $ProjectImplementingPartner = $ProjectImplementingPartner->load('OrgType');
        return resp('1', 'Successful!', $ProjectImplementingPartner, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectImplementingPartner $ProjectImplementingPartner): JsonResponse
    {
        $this->authorizeAny([
            'implementing_partners_update',
        ]);

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'postal_address' => 'required',
            'focal_person' => 'nullable',
            'focal_person_email' => 'nullable',
            'focal_person_contact' => 'nullable',
            'donor_email' => 'nullable',
            'website_link' => 'nullable',
            'org_type' => 'nullable',
        ]);

        if($request->file('logo')){
            $responce=$this->saveImage($request,'implementing_partner_logo');
            $this->input['logo']=$responce;
        }
        $item = $ProjectImplementingPartner->update($this->input);

        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectImplementingPartner $ProjectImplementingPartner): JsonResponse
    {
        $this->authorizeAny([
            'implementing_partners_delete',
        ]);

        $item = $ProjectImplementingPartner->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'project_implementing_partner_id' => 'required|integer|exists:project_implementing_partners,id',
            'logo' => 'required|file|mimes:jpeg,png,jpg,svg|max:5012',
        ]);
        $item = ProjectImplementingPartner::query()->findOrFail($request->project_implementing_partner_id);

        $attachmentPath = $item->logo ?? '';


        if (Storage::disk('public')->exists($attachmentPath)) {
            Storage::disk('public')->delete($attachmentPath);
        }

        if ($request->hasFile('logo')){
            $extension = $request->file('logo')->getClientOriginalExtension();
            $attachmentPath = $request->file('logo')->storeAs('images/implementing_partner', time() . '_logo.' . $extension, 'public');
        }

        $item = $item->update([
            'logo' => $attachmentPath,
        ]);

        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

}
