<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class RoleDetached extends Mailable
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
        $this->modifier = Auth::user();
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.role_detached')
                    ->subject(__('role.role_detached', ['role' => $this->roleName]));
    }
}
