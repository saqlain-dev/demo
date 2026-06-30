<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $emailData;
    /**
     * Create a new job instance.
     */
    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::send([], [], function ($message) {
            $message->to($this->emailData['to'])
                ->subject($this->emailData['subject'])
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->from(config('mail.from.address'), 'LAS-ERP')
                ->html($this->emailData['body']); // Use html() instead of setBody()
        });
    }
}
