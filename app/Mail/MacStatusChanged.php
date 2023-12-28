<?php

namespace App\Mail;

use App\Models\Internet\MacAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MacStatusChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $recipient;
    public string $mac;
    public string $status;

    /**
     * Create a new message instance.
     *
     * @param string $recipient
     * @param MacAddress $macAddress the updated mac address
     *
     */
    public function __construct(string $recipient, MacAddress $macAddress)
    {
        $this->recipient = $recipient;
        $this->mac = $macAddress->mac_address;
        $this->status = $macAddress->translated_state;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.mac_status_changed')
            ->subject(__('internet.mac_status_changed'));
    }
}
