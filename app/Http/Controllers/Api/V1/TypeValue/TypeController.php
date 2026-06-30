<?php

namespace App\Http\Controllers\Api\V1\TypeValue;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'configuration_governance'
        ]);

        $data = Type::query()->where('is_disaggregate',0)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'configuration_governance'
        ]);

        $request->validate([
            'key' => 'required|unique:types|max:150',
            'name' => 'required|max:150',
        ]);
        $item = Type::query()->create($this->input);

        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Type $type): JsonResponse
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'configuration_governance'
        ]);

        return resp('1', 'Successful!', $type, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type $type): JsonResponse
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'configuration_governance'
        ]);

        $request->validate([
            // 'key' => 'required|unique:types|max:150',
            'name' => 'required|max:150',
        ]);
        $item = $type->update($request->except('key')); // key should not be updated

        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type $type): JsonResponse
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'configuration_governance'
        ]);

        $type->delete();
        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_OK);
    }

    public function getTypeValues(string $type): JsonResponse
    {
        $items = Type::getTypeValuesWithTranshed($type);
        return resp('1', 'Successful!', $items, Response::HTTP_OK);
    }

    public function getDisaggregates()
    {
        $items = Type::getDisaggregates();
        return resp('1', 'Successful!', $items, Response::HTTP_OK);
    }
}
