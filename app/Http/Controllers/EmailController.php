<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Send an email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            // Send the email using raw text or HTML body
            Mail::raw($validatedData['body'], function ($message) use ($validatedData) {
                $message->to($validatedData['to'])
                    ->subject($validatedData['subject'])
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            return resp(1, 'Email sent successfully.!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            return resp(0, 'Failed to send email.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
