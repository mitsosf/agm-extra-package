<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */

    protected $user;
    protected $name;
    protected $path;

    public function __construct(User $user, $path)
    {
        $this->user = $user;
        $this->name = $user['name'] . ' ' . $user['surname'] . ' Proof of Payment';
        $this->path = $path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Welcome to the world of AGM Thessaloniki!')->
        from('castaways@agmthessaloniki.org', 'AGM Thessaloniki Castaways')->
        view('mails.sendPaymentConfirmation')->
        with([
            'user' => $this->user,
        ])->
        attach($this->path, [
            'as' => $this->name . '.pdf',
            'mime' => 'application/pdf',
        ]);
    }
}
