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

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $type;
    public $user_name;
    public $token;

    public function __construct($type, $user_name, $token=null)
    {
        $this->type = $type;
        $this->user_name = $user_name;
        $this->token = $token;
    }

    public function build()
    {
        $emailContent = EmailTemplate::find($this->type);

        $placeholders['{{ name }}'] = $this->user_name;

        if(!empty($this->token)) {
            $placeholders1['{{ link }}'] = '<a href="'.route('user.showResetForm', ['token' => $this->token]).'">Reset Password</a>';
            $content1 = Str::of($emailContent->body1);
            foreach ($placeholders1 as $key => $value) {
                $emailContent->body1 = $content1->replace($key, $value);
            }
        }

        $content = Str::of($emailContent->content);

        foreach ($placeholders as $key => $value) {
            $emailContent->content = $content->replace($key, $value);
        }
        return $this->subject($emailContent->subject)
                    ->view('user.emails.mail')
                    ->with('emailContent', $emailContent);
    }

}
