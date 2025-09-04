<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SendWebFormMailForCilent extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $client_name;
    public $type;

    public $check_number;

    public $total;

    public function __construct($type, $client_name, $check_number, $total)
    {
        $this->type = $type;
        $this->client_name = $client_name;
        $this->check_number = $check_number;
        $this->total = $total;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders = [
            '{{ client_name }}'  => $this->client_name ?? '',
            '{{ check_number }}'  => $this->check_number ?? '',
            '{{ total }}'  => $this->total ?? '',
        ];

        $fields = ['subject', 'content', 'body1', 'body2'];

        foreach ($fields as $field) {
            if (!empty($emailContent->{$field})) {
                $content = Str::of($emailContent->{$field});
                foreach ($placeholders as $key => $value) {
                    $content = $content->replace($key, $value);
                }
                $emailContent->{$field} = $content;
            }
        }

        $mail = $this->subject($emailContent->subject)
                     ->view('user.emails.mail')
                     ->with('emailContent', $emailContent)
                     ->with('sendWebFormMailForClient', 1);

        return $mail;
    }

}
