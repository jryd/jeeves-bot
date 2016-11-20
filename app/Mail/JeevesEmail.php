<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class JeevesEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('jeeves@bannister.me', 'Jeeves Bot')
                    ->subject('A message from Jeeves Bot')
                    ->view('emails.message');
    }
}
