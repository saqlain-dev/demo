<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookIssued;
use App\Models\Admin\Library\BookVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_employee_portal',
            'books_record_view',
        ]);

        $data['books'] = Book::with('BookCategory','book_type','location','created_by','updated_by')->withCount('bookVariant')->orderByDesc('id')->get();

        $data['issuedBooks'] = BookIssued::where('status', 1)->whereNull('return_date')->count();

        $data['totalBooks'] = $data['books']->count();

        $data['totalBookWithVariants'] = $data['books']->sum('book_qty');

        $data['availableBooks'] = $data['totalBookWithVariants'] - $data['issuedBooks'];

        $data['returnedBooks'] = BookIssued::where('status', 2)->count();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'books_record_create',
        ]);

        $this->input = $request->all();
        $request->validate([
            //'book_tag' => 'required',
            'book_type' => 'required',
            'book_name' => 'required',
            'book_author' => 'required',
            'book_category' => 'required',
            'shelf_no' => 'required',
            'date' => 'required',
            'description' => 'required',
            //'book_image' => 'required',
            'book_qty' => 'required',
        ]);

        if($request->hasFile('book_image')) {
            $responce = $this->saveBookImage($request, 'book_images');
            if ($responce) {
                $this->input['book_image'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('books') + 1 as nextID");
            $nextID = $statement[0]->nextID;

            // Format the book tag
            $bookTag = 'bk/' . sprintf('%04d', $nextID);

            // Add book_tag to input array
            $this->input['book_tag'] = $bookTag;

            $item = Book::query()->create($this->input);

            if($this->input['book_qty'] >= 1){

                for ($i=1; $i <= $this->input['book_qty']; $i++) {
                    $bookVariant = new BookVariant();
                    $bookVariant->book_id = $item->id;
                    $bookVariant->book_tag = $item->book_tag . '/' . $i;
                    $bookVariant->shelf_no = $item->shelf_no;
                    $bookVariant->book_type = $item->book_type;
                    $bookVariant->save();
                }
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
    public function show(Book $book): JsonResponse
    {
        $this->authorizeAny([
            'books_record_view',
            'manage_employee_portal',
        ]);

        $data['book'] = $book->load('BookCategory','book_type','location','created_by','updated_by','bookVariant.book_type');

        $data['books_issued_summary'] = BookIssued::query()->where('book_id',$data['book']->id)->with('BookId','EmployeeId','created_by')->orderByDesc('id')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $this->authorizeAny([
            'books_record_update'
        ]);

        $request->validate([
            //'book_tag' => 'required',
            'book_type' => 'required',
            'book_name' => 'required',
            'book_author' => 'required',
            'book_category' => 'required',
            'shelf_no' => 'required',
            'date' => 'required',
            'description' => 'required',
            'book_qty' => 'required',
        ]);
        if($request->hasFile('book_image')) {
            $responce = $this->saveBookImage($request, 'book_images');
            if ($responce) {
                $this->input['book_image'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $book->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveBookImage($request,$folder){

        $file = $request->file('book_image');
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

    public function getBookSummary()
    {
        $this->authorizeAny([
            'manage_books_summary'
        ]);

        // Fetch books with the count of issued books
        //$books = Book::withCount('booksIssued')->get();
        $books = Book::withCount([
            'booksIssued' => function ($query) {
                $query->where('status', 1)
                    ->whereNull('return_date');
            }
        ])->get();

        // Calculate the Distinct totals
        $totalBooksM = $books->count(); // Count of distinct books

        $totalBooks = $books->sum('book_qty');

        $totalIssuedBooks = BookIssued::where('status', 1)->whereNull('return_date')->count();
        $totalAvailableBooksM = $totalBooks -  $totalIssuedBooks;

        $totalAvailableBooks = $totalBooks - $totalIssuedBooks;

        // Create the summary for each book
        $summary = $books->map(function ($book) {
            return [
                'book_id' => $book->id,
                'book_name' => $book->book_name,
                'total_quantity' => $book->book_qty,
                'issued_quantity' => $book->books_issued_count,
                'available_quantity' => $book->book_qty - $book->books_issued_count
            ];
        });

        // Add the totals to the response
        $response = [
            'books' => $summary,
            'totals' => [
                'total_books' => $totalBooks,
                'total_issued_books' => $totalIssuedBooks,
                'total_available_books' => $totalAvailableBooks,
            ],
            'BooksTotal' => [
                'books' => $totalBooksM,
                'issued_books' => $totalIssuedBooks,
                'available_books' => $totalAvailableBooksM,
            ]
        ];

        return response()->json($response);
    }

    public function getBookIssuedSummary(Request $request,Book $book)
    {

        try {
            DB::beginTransaction();
            $data['books_issued_summary'] = BookIssued::query()->where('book_id',$book->id)->with('BookId','EmployeeId','created_by')->orderByDesc('id')->get();
            DB::commit();
            return resp('1', 'Successfully!', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorizeAny([
            'books_record_delete'
        ]);

        $item = $book->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
