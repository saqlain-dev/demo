<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\ScaleRating;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScaleRatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['scale_rating']=ScaleRating::query()->with('stage')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'scale_stage' => 'required|integer|unique:scale_ratings,scale_stage',
            'probability' => 'required|numeric',
            'rating' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $rating=ScaleRating::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $rating->load('stage'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getScaleDropDown()
    {
        $data['sales_stage']=Type::getTypeValues('sales-stage');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(ScaleRating $scaleRating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScaleRating $scale_rating)
    {
        $request->validate([
            'scale_stage' => [
                'required',
                'integer',
                Rule::unique('scale_ratings', 'scale_stage')->ignore($scale_rating->id), // Ignore current record
            ],
            'probability' => 'required|numeric',
            'rating' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $scale_rating->update($this->input);
            $scale_rating->refresh();

            DB::commit();
            return resp(1, 'Successful!', $scale_rating->load('stage'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScaleRating $scale_rating)
    {
        $scale_rating->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
}
