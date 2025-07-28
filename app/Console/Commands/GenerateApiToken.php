<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-api-token {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');

        // Validate user ID
        if (!is_numeric($userId)) {
            $this->error('Invalid user ID provided.');
            return 1;
        }

        // Find the user
        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->error('User not found.');
            return 1;
        }

        // Generate a new API token
        $token = $user->createToken('API Token')->plainTextToken;

        // Output the token
        $this->info("API Token for user {$user->name}: {$token}");

        return 0;
    }
}
