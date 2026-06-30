<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\BranchOffice;
use App\Models\HeadOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BranchOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $branchOffices= BranchOffice::with('headOffices')->get();
        return resp(1,'Successful!', $branchOffices->toArray(),Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'contact' => 'required',
            'head_office_id' => 'required',
        ]);
        $branchOffices=BranchOffice::query()->create($request->all());
        return resp(1,'Successful!', $branchOffices->toArray(),Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(BranchOffice $branchOffice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BranchOffice $branchOffice)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'contact' => 'required',
            'head_office_id' => 'required',
        ]);
        $branchOffice->head_office_id=$request->head_office_id;
        $branchOffice->name=$request->name;
        $branchOffice->location=$request->location;
        $branchOffice->contact=$request->contact;
        $branchOffice->status=$request->status;
        $branchOffice->save();
        return resp(1,'Successful!', $branchOffice->toArray(),Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BranchOffice $branchOffice)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $branchOffice->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }
}
