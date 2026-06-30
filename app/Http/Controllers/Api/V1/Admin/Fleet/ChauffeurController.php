<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Chauffeur;
use App\Models\Admin\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ChauffeurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Chauffeur::where(['status'=>1])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'chauffeur_name' => 'required',
                'chauffeur_code' => 'required',
                'chauffeur_cnic' => 'required',
                'contact_number' => 'required',
                'address' => 'required',
            ]);
            $item = Chauffeur::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Chauffeur $chauffeur): JsonResponse
    {
        return resp('1', 'Successful!', $chauffeur, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chauffeur $chauffeur)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'chauffeur_name' => 'required',
                'chauffeur_code' => 'required',
                'chauffeur_cnic' => 'required',
                'contact_number' => 'required',
                'address' => 'required',
            ]);
            $item = $chauffeur->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chauffeur $chauffeur): JsonResponse
    {
        $item = $chauffeur->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
