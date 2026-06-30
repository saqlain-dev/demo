<?php

namespace App\Http\Controllers\Api\V1\Donar;

use App\Http\Controllers\Controller;
use App\Models\Donar\DonarProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DonarProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'donors_view',
            'manage_audit_grant_management',
            'manage_donors',
            'project_create',
            'project_view'
        ]);

        $data['donar_profile_list']=DonarProfile::with('OrgType')->get();
        return resp('1', 'Donor profile detail', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'donors_create',
            'project_create',
            'project_view'
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'donar_name' => 'required',
                'donar_contact' => 'required',
                'donar_address' => 'required',
            ]);
            if($request->file('donar_logo')){
                $responce=$this->saveDonarProfile($request,'donarLogo');
                $this->input['donar_logo']=$responce;
            }

            $donar=DonarProfile::query()->create($this->input);

            DB::commit();

            return resp('1', 'Donor profile added Successfully!', $donar, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveDonarProfile($request,$folder){

        $file = $request->file('donar_logo');
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
    public function show(DonarProfile $donor_profile)
    {
        $this->authorizeAny([
            'donors_view',
            'manage_audit_grant_management',
            'project_create',
            'project_view'
        ]);

        $data['donar_profile'] = $donor_profile->load(['OrgType']);
        return resp('1', 'Donor profile detail', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonarProfile $donor_profile)
    {
        $this->authorizeAny([
            'donors_update',
            'project_create',
            'project_view'
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'donar_name' => 'required',
                'donar_contact' => 'required',
                'donar_address' => 'required',
            ]);
            if($request->file('donar_logo')){
                $responce=$this->saveDonarProfile($request,'donarLogo');
                $this->input['donar_logo']=$responce;
            }

            DonarProfile::query()->where('id',$donor_profile->id)->update($this->input);
            $donor_profile->refresh();
            DB::commit();

            return resp('1', 'Donor profile updated Successfully!', $donor_profile, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonarProfile $donor_profile)
    {
        $this->authorizeAny([
            'donors_delete',
            'project_create',
            'project_view'
        ]);

        $donarDelete=$donor_profile->delete();
        return resp('1', 'Donor profile deleted', $donarDelete, Response::HTTP_OK);
    }
}
