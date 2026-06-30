<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Models\Admin\Fleet\FeedBackQuestion;

class FeedBackQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-admin',
        ]);

        $data = FeedBackQuestion::whereNull('deleted_at')->get();
        return resp('1', 'Successfull!', $data, Response::HTTP_OK);
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
            'configuration-admin',
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'question' => 'required',
                'type' => 'required', //comuter and driver
             ]);
            $item = FeedBackQuestion::query()->create($this->input);
            DB::commit();
            return resp('1', 'Added Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to add question. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorizeAny([
            'configuration-admin',
        ]);

        $questions = FeedBackQuestion::query()->findOrFail($id);
        return resp('1', 'Successful!', $questions, Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizeAny([
            'configuration-admin',
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'question' => 'required',
                'type' => 'required',
             ]);
            $item = FeedBackQuestion::query()->findOrFail($id);
            $item->update($this->input);
            DB::commit();
            return resp('1', 'Added Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to add question. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'configuration-admin',
        ]);

        try {
           $feedBackQuestion = FeedBackQuestion::query()->findOrFail($id);

           $item = $feedBackQuestion->delete();

            return resp('1', 'Question Deleted Successfully!', $item, Response::HTTP_OK);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return resp('0', 'Cannot delete this feedback question as it is assigned in requisition!', Response::HTTP_CONFLICT);
            }
            return resp('0', 'Failed to delete feed back question', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
        return resp('0', 'Failed to delete feed back question', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
