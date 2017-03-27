<?php

namespace App\Mail;

use App\Models\Reports;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LinkReported extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var int
     */
    public $report_id;

    public function __construct($report_id)
    {
        $this->report_id = $report_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $admin = User::find(2);
        $report = Reports::with(['link', 'user'])->find($this->report_id);

        $this->subject = 'Link Reported';

        return $this->to($admin->email, $admin->username)
            ->replyTo($report->email)
            ->view('emails.report')
            ->with([
                'to'      => $admin->email,
                'subject' => $this->subject,
                'report'  => $report,
            ]);
    }
}
