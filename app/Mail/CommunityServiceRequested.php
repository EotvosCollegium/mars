<?php

namespace App\Mail;

use App\Models\CommunityService;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommunityServiceRequested extends Mailable
{
    use Queueable, SerializesModels;

    public User $recipient;
    public string $description;
    public User $requester; 

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CommunityService $communityService)
    {
        $this->recipient = $communityService->approver;
        $this->description = $communityService->description;
        $this->requester = $communityService->requester;
    }

    /**
     * Build the message.
     *
     * @return $this    
     */
    public function build()
    {
        return $this->markdown('emails.community_service_requested')
                    ->subject(__('mail.community_service_requested'));
    }
}
