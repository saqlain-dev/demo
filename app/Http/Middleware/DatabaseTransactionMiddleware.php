<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DatabaseTransactionMiddleware
{
    public function handle($request, Closure $next)
    {
        //dd('inside the middelware');
        Log::info('Middleware executed');
        //
        // Start the database transaction
        DB::beginTransaction();

        try {

            // Process the request
            $response = $next($request);
            //return $response;
            // Commit the transaction if everything succeeds
            DB::commit();
        } catch (\Exception $e) {

            // Roll back the transaction if an exception occurs
            DB::rollback();

            // Check if the exception is a database-related error
            if ($e instanceof QueryException) {
                // Handle the database exception here
                \Log::error('Database Error: ' . $e->getMessage());

                // Return a custom response for database errors
                return response()->json([
                    'success' => false,
                    'message' => 'Database error',
                    'error' => $e->getMessage()
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }

            // Re-throw other types of exceptions
            throw $e;
        }

        return $response;
    }
}
