<?php

namespace App\Http\Controllers\Api\V1\HR\Leaves;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\BranchOffice;
use App\Models\HR\Leaves\SpecialHoliday;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use DateTime;
class SpecialHolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);


        $data['special_holidays']=SpecialHoliday::query()->with(['religionDetail','branch_office_detail','genderDetail','religiousSect'])->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    public function specialHolidayDropDown()
    {
        $data['religion']=Type::getTypeValues('employee-religion');
        $data['religious_sect']=Type::getTypeValues('religious-sect');
        $data['gender']=Type::getTypeValues('employee-gender');
        $data['branch_offices']=BranchOffice::all();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'religion' => 'required|integer',
            'gender' => 'required|integer',
            'branch_office' => 'required|integer',
            'religious_sect' => 'required|integer',
            'leave_date' => 'required|date_format:Y-m-d',
        ]);
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $f_end_date=date('Y-m-d',strtotime($financialYear->end_date));
        $f_start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $date=date('Y-m-d',strtotime($request->leave_date));
        if ($this->isInFinancialYear($date, $f_start_date, $f_end_date)) {
            try {
                DB::beginTransaction();

                $this->input['leave_date'] = date('Y-m-d', strtotime($request->leave_date));

                $specialHoliday = SpecialHoliday::query()->create($this->input);
                DB::commit();
                return resp('1', 'Special Holiday added Successfully!', $specialHoliday->load('religionDetail','branch_office_detail'), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SpecialHoliday $specialHoliday)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        return resp('1', 'Successfully!', $specialHoliday->load('religionDetail','genderDetail','branch_office_detail','religiousSect'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpecialHoliday $specialHoliday)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'religion' => 'required|integer',
            'religious_sect' => 'required|integer',
            'gender' => 'required|integer',
            'branch_office' => 'required|integer',
            'leave_date' => 'required|date_format:Y-m-d',
        ]);
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $f_end_date=date('Y-m-d',strtotime($financialYear->end_date));
        $f_start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $date=date('Y-m-d',strtotime($request->leave_date));
        if ($this->isInFinancialYear($date, $f_start_date, $f_end_date)) {
            try {
                DB::beginTransaction();

                $this->input['leave_date'] = date('Y-m-d', strtotime($request->leave_date));

                 SpecialHoliday::query()->find($specialHoliday->id)->update($this->input);
                $specialHoliday->refresh();
                DB::commit();
                return resp('1', 'Special Holiday updated Successfully!', $specialHoliday->load('religionDetail','branch_office_detail'), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpecialHoliday $specialHoliday)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        try {
            DB::beginTransaction();
            $specialHoliday->delete();
            DB::commit();
            return resp('1', 'Special Holiday deleted Successfully!', null, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to delete record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function isInFinancialYear($date, $financialYearStart, $financialYearEnd) {
        // Convert dates to DateTime objects for easy comparison
        $date = new DateTime($date);
        $financialYearStart = new DateTime($financialYearStart);
        $financialYearEnd = new DateTime($financialYearEnd);

        // Check if the date is between the financial year start and end dates
        return ($date >= $financialYearStart && $date <= $financialYearEnd);
    }
}
