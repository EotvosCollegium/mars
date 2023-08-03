<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoleAttached extends Mailable
{
    use SerializesModels;

    public string $recipient;
    public string $roleName;
    public string $objectName;
    public ?User $modifier;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $recipient, string $roleName, string $objectName)
    {
        $this->recipient = $recipient;
        $this->roleName = $roleName;
        $this->objectName = $objectName;
        $this->modifier = user();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.role_attached')
                    ->subject(__('role.role_attached', ['role' => $this->roleName]));
    }
}
