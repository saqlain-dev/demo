<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Governance\BoardMeeting;
use App\Models\Questionnaire\QuestionnaireForm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DesginationController extends Controller
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

        $data = Designation::with(['created_by','updated_by','reportTo'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
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
        ]);
        $item = Designation::query()->create($this->input);

        return resp('1','Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $item = Designation::query()->with(['created_by','updated_by','reportTo'])->findOrFail($id);
        return resp('1','Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $item = Designation::query()->findOrFail($id);
        $request->validate([
            'name' => 'required',
        ]);
        $item->update($this->input);

        return resp('1','Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorizeAny([
            'configuration-admin',
            'configuration-hr',
        ]);

        $designation = Designation::findOrFail($id);

        if ($designation->reportTo()->exists()) {
            return response()->json([
                'status' => 0,
                'message' => 'This designation has reports associated with it.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $designation->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Record deleted successfully.',
        ], Response::HTTP_OK);
    }

    public function getEmployeeOrganogram(string $id)
    {
        $item = Designation::query()->findOrFail($id);
        return resp('1','Successful!', $item, Response::HTTP_OK);
    }

    public function getOrganogram(Request $request)
    {
        $query = Designation::with('employees');

        if ($request->departmentId !== null) {
            $query->whereHas('employees', function ($query) use ($request) {
                $query->where('department_id', $request->departmentId );
            });
        }

        if ($request->desginationId !== null){
            $topLevelDesignations = $query
                ->where('id',$request->desginationId)
                ->get();
        } else{
            $topLevelDesignations = $query
                ->whereNull('report_to')
                ->get();
        }

        $organogram = [];

        foreach ($topLevelDesignations as $topLevelDesignation) {
            $organogram[] = $this->buildHierarchy($topLevelDesignation, $request->departmentId);
        }

        return $organogram;
    }

    protected function buildHierarchy($designation, $departmentId)
    {
        $hierarchy = [
            'designation' => $designation,
            'subordinates' => [],
        ];

        $query = Designation::with('employees');

        if ($departmentId !== null) {
            $query->whereHas('employees', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        }

        $subordinates = $query
            ->where('report_to', $designation->id)
            ->get();

        foreach ($subordinates as $subordinate) {
            $hierarchy['subordinates'][] = $this->buildHierarchy($subordinate, $departmentId);
        }

        return $hierarchy;
    }

}
