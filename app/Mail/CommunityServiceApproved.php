<?php

namespace App\Mail;

use App\Models\CommunityService;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommunityServiceApproved extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public User $approver;
    public string $description;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CommunityService $communityService)
    {
        $this->recipient = $communityService->requester;
        $this->approver = $communityService->approver;
        $this->description = $communityService->description;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.community_service_approved')
                    ->subject(__('mail.community_service_approved'));
    }
}
