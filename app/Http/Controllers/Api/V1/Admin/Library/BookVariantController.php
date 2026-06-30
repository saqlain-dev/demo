<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use Illuminate\Http\Request;
use App\Models\Admin\Library\Book;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Admin\Library\BookVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Book::with('bookVariant')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->input = $request->all();
        $request->validate([
            'book_id' => 'required',
            // 'book_tag' => 'nullable',
            'shelf_no' => 'required',
            'book_type' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $book = Book::with('bookVariant')->where('id', $request->book_id)->first();

            if(!$book->bookVariant){
                //added 1 into tag if it is first variant
                $this->input['book_tag'] = substr($book->book_tag, 0, strrpos($book->book_tag, '/') + 1) . 1;
            }else{
                //if already variants are already exist then selected latest one to get tag and added number
                $latestVarient = $book->bookVariant()->latest('created_at')->first();

                $bookTag = $latestVarient->book_tag;
                $lastDigit = substr(strrchr($bookTag, '/'), 1);
                $incrementedDigit = (int)$lastDigit + 1;
                $newBookTag = substr($bookTag, 0, strrpos($bookTag, '/') + 1) . $incrementedDigit;

                $this->input['book_tag'] = $newBookTag;
            }

            $item = BookVariant::query()->create($this->input);
            if($item){
                $book->book_qty = $book->book_qty + 1;
                $book->save();
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BookVariant $bookVariant): JsonResponse
    {
        return resp('1', 'Successful!', $bookVariant, Response::HTTP_OK);
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
    public function update(Request $request, BookVariant $bookVariant)
    {
        $this->input = $request->all();
        $request->validate([
            'shelf_no' => 'required',
            'book_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $bookVariant->update($request->all());
            DB::commit();
            return resp('1', 'Record Update Successfully!', $item, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookVariant $bookVariant): JsonResponse
    {
        $item = $bookVariant->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
