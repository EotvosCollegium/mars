<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class EpistolaCollegii extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $epistolas;
    public $previewDate;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($epistolas, $previewDate = null)
    {
        $this->epistolas = $epistolas;
        $this->theme = 'epistola';
        $this->previewDate = $previewDate ? Carbon::parse($previewDate) : now();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Epistola Collegii - '. $this->previewDate->format('Y. m. d.'))
            ->markdown('emails.epistola', ['news' => $this->epistolas, 'preview_date' => $this->previewDate]);
    }
}
