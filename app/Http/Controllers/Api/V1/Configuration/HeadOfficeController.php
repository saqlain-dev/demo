<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\HeadOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HeadOfficeController extends Controller
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

        $headOffices= HeadOffice::all();
        return resp(1,'Successful!', $headOffices->toArray(),Response::HTTP_CREATED);
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

        //dd($request->all());
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'contact' => 'required',
        ]);
        $Headoffice=HeadOffice::query()->create($request->all());
        return resp(1,'Successful!', $Headoffice->toArray(),Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(HeadOffice $headOffice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeadOffice $headOffice)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'contact' => 'required',
        ]);
        $headOffice->name=$request->name;
        $headOffice->location=$request->location;
        $headOffice->contact=$request->contact;
        $headOffice->status=$request->status;
        $headOffice->save();
        return resp(1,'Successful!', $headOffice->toArray(),Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeadOffice $headOffice)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        if($headOffice->branches()->count() > 0){
            return resp(0,'Cannot be deleted. Head Office has child branches.', $headOffice->toArray(),Response::HTTP_CREATED);
        }
        $headOffice->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }
}
