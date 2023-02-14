<?php

namespace App\Mail;

use App\Models\CommunityService;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommunityServiceStatusChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $recipient;
    public User $approver;
    public string $description;
    public bool $approved;

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
        $this->approved = $communityService->approved;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.community_service_status_changed')
                    ->subject('Közösségi tevékenység státusza megváltozott');
    }
}
