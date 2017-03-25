<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivateUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $code;

    /**
     * ActivateUser constructor.
     *
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject = env('SITE_NAME') . ' - Account Activation Link';

        return $this->to($this->user->email, $this->user->username)
            ->view('emails.activate')
            ->text('emails.activate-plain')
            ->with([
                'to'      => $this->user->email,
                'subject' => $this->subject,
                'code'    => $this->code,
            ]);
    }
}
