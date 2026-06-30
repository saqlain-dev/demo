<?php

namespace App\Http\Controllers\Api\V1\TypeValue;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TypeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = TypeValue::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'type_id' => 'required|max:150',
            'parent_id' => 'nullable|exists:type_values,id',
        ]);
        $item = TypeValue::query()->create($this->input);

        return resp('1','Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeValue $typeValue): JsonResponse
    {
        return resp('1','Successful!', $typeValue, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeValue $typeValue): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'type_id' => 'required|max:150',
            'parent_id' => 'nullable|exists:type_values,id',
        ]);
        $item = $typeValue->update($this->input);

        return resp('1','Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeValue $typeValue): JsonResponse
    {
        $typeValue->delete();
        return resp('1','Record Deleted Successfully!', [], Response::HTTP_OK);
    }

    public function restore($id)
    {
        // Call the restoreValue method in the model
        $restoredValue = TypeValue::restoreValue($id);

        if ($restoredValue) {
            return resp('1','Record Restored Successfully!', [], Response::HTTP_OK);

        }

        return resp('0','Record Not Restored!', [], Response::HTTP_NOT_FOUND);

    }
}
