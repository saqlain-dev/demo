<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PreGrossSalaryAllowances;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PreGrossSalaryAllowancesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['gross_allowances']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'allowance_type' => 'required|array',
            'allowance_type.*' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // Check if the allowance type exists in the database
                    $exists = DB::table('pre_gross_salary_allowances')->where('allowance_type', $value)->exists();
                    if ($exists) {
                        $fail("The allowance type '{$value}' already exists in the database.");
                    }
                },
            ],
            'allowance_percentage' => ['required', 'array', function ($attribute, $value, $fail) {
                if (array_sum($value) > 100) {
                    $fail('The sum of allowance percentages must not exceed 100.');
                }
            }],
            'allowance_percentage.*' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $input = $request->all();

            $data = []; // Initialize empty array
            foreach ($input['allowance_type'] as $key => $name) {
                $data[] = [
                    'allowance_type' => $name,
                    'allowance_percentage' => $input['allowance_percentage'][$key]
                ];
            }
           PreGrossSalaryAllowances::query()->insert($data);
            DB::commit();
            $data['pre_gross_allowance']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
            return resp('1', 'Pre gross allowance added Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PreGrossSalaryAllowances $preGrossSalaryAllowances)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PreGrossSalaryAllowances $pre_gross_salary_allowance)
    {


        $request->validate([
            'allowance_type' => 'required|array',
            'allowance_type.*' => 'required|integer',
            'allowance_percentage' => ['required', 'array', function ($attribute, $value, $fail) {
                if (array_sum($value) > 100) {
                    $fail('The sum of allowance percentages must not exceed 100.');
                }
            }],
            'allowance_percentage.*' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $input = $request->all();

            $data = [];
            foreach ($input['allowance_type'] as $key => $name) {
                $data[] = [
                    'id' => $input['id'][$key] ?? null, // Include id if available
                    'allowance_type' => $name,
                    'allowance_percentage' => $input['allowance_percentage'][$key]
                ];
            }
            foreach ($data as $item) {
                if (!empty($item['id'])) {
                    PreGrossSalaryAllowances::query()->where('id', $item['id'])->update([
                        'allowance_type' => $item['allowance_type'],
                        'allowance_percentage' => $item['allowance_percentage']
                    ]);
                }else {
                    // Insert a new record if id does not exist
                    PreGrossSalaryAllowances::create([
                        'allowance_type' => $item['allowance_type'],
                        'allowance_percentage' => $item['allowance_percentage']
                    ]);
                }
            }
            DB::commit();
            $data['pre_gross_allowance']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
            return resp('1', 'Pre gross allowance updated Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreGrossSalaryAllowances $preGrossSalaryAllowances)
    {
        //
    }

    public function grossSalaryDropDown()
    {
        $data['pre_gross_allowance_type']=Type::getTypeValues('pre-gross-sallary-allowance');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
}
