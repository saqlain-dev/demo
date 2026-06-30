<?php

namespace App\Http\Controllers\Api\V1\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Finance\LasConfiguration;

class LasConfigurationController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $data['listing'] = LasConfiguration::with('bankInfo')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $this->input = $request->input();
        $request->validate([
            'organization_name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'contact' => 'required',
            'email' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('official_logo')) {
                $file = $request->file('official_logo');
                $response = $this->saveAttachment($file, 'lasConfiguration');
                if ($response) {
                    $this->input['official_logo'] = $response;
                }
            }

            $item = LasConfiguration::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    public function saveAttachment($file, $folder){


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
    public function show($id)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $lasConfiguration = LasConfiguration::with('bankInfo')->findOrFail($id);
        return resp('1', 'Successful!', $lasConfiguration, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $lasConfiguration = LasConfiguration::findOrFail($id);

        $this->input = $request->except('_method');

        $request->validate([
            'organization_name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'contact' => 'required',
            'email' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('official_logo')) {
                $file = $request->file('official_logo');
                $response = $this->saveAttachment($file, 'lasConfiguration');
                if ($response) {
                    $this->input['official_logo'] = $response;
                }
            }

            $item = $lasConfiguration->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $lasConfiguration = LasConfiguration::findOrFail($id);
        $lasConfiguration->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
